<?php

namespace App\Form;

use App\Entity\Property;
use App\Form\DataTransformer\SlugTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyType extends AbstractType
{
    private SlugTransformer $slugTransformer;

    public function __construct(SlugTransformer $slugTransformer)
    {
        $this->slugTransformer = $slugTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('slug', null, [
                'required' => false,
            ]);

        // Use the transformer for the slug field
        $builder->get('slug')->addModelTransformer($this->slugTransformer);

        // Regenerate the slug if the title has changed
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $property = $event->getData();
            $form = $event->getForm();

            // Regenerate slug if the title has changed
            $originalTitle = $form->getConfig()->getOption('original_title');

            if ($property->getTitle() !== $originalTitle) {
                $property->setSlug(
                    $this->slugTransformer->reverseTransform($property->getTitle())
                );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
            'original_title' => null, // Pass the original title from the controller
        ]);
    }
}
