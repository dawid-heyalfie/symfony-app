Feature: Fetching properties through API
    In order to browse properties
    As a user
    I want to be able to fetch properties by filters, pagination, or slug

    # 1. Fetch all properties (no filters)
    Scenario: Fetch all properties
        Given I request "api/properties"
        Then I should receive a successful response

    # 2. Fetch a property by slug
    Scenario: Fetch property by slug
        Given I request "api/properties/cozy-country-house"
        Then I should see "Cozy Country House"

    # 3. Fetch a property by a non-existing slug
    Scenario: Fetch a non-existing property
        Given I request "api/properties/non-existing-slug"
        Then I should receive a "404" response with error "Property not found"

    # 4. Fetch properties filtered by title
    Scenario: Fetch properties with a specific title
        Given I request "api/properties?title=Cozy Country House"
        Then I should see "Cozy Country House"

    # 5. Fetch properties filtered by minimum price
    Scenario: Fetch properties with a minimum price
        Given I request "api/properties?minPrice=500000"
        Then I should see at least one property

    # 6. Fetch properties filtered by maximum price
    Scenario: Fetch properties with a maximum price
        Given I request "api/properties?maxPrice=1000000"
        Then I should see at least one property

    # 7. Fetch properties filtered by minPrice and maxPrice
    Scenario: Fetch properties within a price range
        Given I request "api/properties?minPrice=500000&maxPrice=1000000"
        Then I should see at least one property

    # 8. Fetch properties with pagination (first page)
    Scenario: Fetch properties with pagination
        Given I request "api/properties?page=1&limit=10"
        Then I should see "meta" in response

    # 9. Fetch properties with invalid filter
    Scenario: Fetch properties with an invalid filter
        Given I request "api/properties?invalidFilter=value"
        Then I should receive a successful response

    # 10. Fetch properties when no results match filters
    Scenario: Fetch properties with no matching filters
        Given I request "api/properties?title=NonExistentTitle"
        Then I should receive an empty data response
