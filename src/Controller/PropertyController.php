<?php

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\PropertyRepository;
use App\Service\PaginationService;
use App\Service\PropertyFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class PropertyController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PropertyFilterService $filterService;
    private PaginationService $paginationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PropertyFilterService $filterService,
        PaginationService $paginationService
    ) {
        $this->entityManager = $entityManager;
        $this->filterService = $filterService;
        $this->paginationService = $paginationService;
    }
    #[Route('api/properties', name: 'app_property_index', methods: ['GET'])]
    public function index(
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse {
        $filters = $request->query->all();

        $queryBuilder = $this->entityManager->getRepository(Property::class)
            ->createQueryBuilder('p');

        $this->filterService->applyFilters($queryBuilder, $filters);
        $paginationMeta = $this->paginationService->paginate($queryBuilder, $filters);
        $properties = $queryBuilder->getQuery()->getResult();
        $jsonContent = $serializer->serialize($properties, 'json');

        return new JsonResponse([
            'data' => json_decode($jsonContent),
            'meta' => $paginationMeta,
        ]);
    }
    #[Route('api/properties/{slug}', name: 'app_property_show', methods: ['GET'])]
    public function show(string $slug, PropertyRepository $propertyRepository): Response
    {
        $property = $propertyRepository->findOneBySlug($slug);

        if (!$property) {
            return new JsonResponse(['error' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($property);
    }

    #[Route('api/auth/properties', name: 'app_property_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        FormFactoryInterface $formFactory
    ): Response {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_PROPERTY_MANAGER')
        ) {
            return new JsonResponse(
                [
                    'error' => 'Access denied. You do not have the required permissions.'
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $property = new Property();

        $form = $formFactory->create(PropertyType::class, $property);

        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Assign the currently authenticated user as the owner
        $property->setOwner($this->getUser());

        $entityManager->persist($property);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($property, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }



    #[Route('api/auth/properties/{id}', name: 'app_property_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Property $property,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        FormFactoryInterface $formFactory
    ): Response {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !($this->isGranted('ROLE_PROPERTY_MANAGER') &&
                $this->getUser()->getId() === $property->getOwnerObject()->getId())
        ) {
            return new JsonResponse(
                [
                    'error' => 'Access denied. You do not have the required permissions.'
                ],
                Response::HTTP_FORBIDDEN
            );
        }
        $form = $formFactory->create(PropertyType::class, $property);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse($serializer->serialize($property, 'json'), Response::HTTP_OK, [], true);
    }

    #[Route('api/auth/properties/{id}', name: 'app_property_delete', methods: ['DELETE'])]
    public function delete(Property $property, EntityManagerInterface $entityManager): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !($this->isGranted('ROLE_PROPERTY_MANAGER') &&
                $this->getUser()->getId() === $property->getOwnerObject()->getId())
        ) {
            return new JsonResponse(
                [
                    'error' => 'Access denied. You do not have the required permissions.'
                ],
                Response::HTTP_FORBIDDEN
            );
        }
        $propertyId = $property->getId();
        $entityManager->remove($property);
        $entityManager->flush();

        return new JsonResponse("Property with ID: $propertyId deleted.", Response::HTTP_OK);
    }


}
