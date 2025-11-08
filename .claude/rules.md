# Contanta Project Rules

## Coding and Generation Rules

1. **Architecture**
    - Use **Domain-Driven Design (DDD)** strictly.
    - Each backend feature must belong to a domain in `app/Domains/{DomainName}`.
    - Cross-domain communication should occur only via **service contracts** or **facades**.
    - Keep controllers thin — delegate logic to Services.

2. **Laravel Backend Rules**
    - Use **Form Requests** for input validation.
    - Use **Service Contracts and Providers** for dependency binding.
    - **Unit tests must reside inside the domain** under 
      - `app/Domains/{DomainName}/tests/UnitTests`
      - `app/Domains/{DomainName}/tests/FeatureTests`
    - 
        - Examples:
            - `MenuBuilderServiceTest.php`
            - `MenuModelTest.php`
            - `MenuBuilderControllerTest.php`
            - `MenuBuilderAdminControllerTest.php`
            - `MenuBuilderServiceContractTest.php`
            - `MenuBuilderFacadeTest.php`
            - `MenuBuilderServiceProviderTest.php`
            - `MenuBuilderRoutesTest.php`
            - `MenuBuilderFormRequestTest.php`
            - `MenuBuilderInputTest.php`
            - `MenuBuilderConstantTest.php`
            - `MenuBuilderSubdomainTests.php`
            - etc.
    - Controllers must return **data or Inertia-compatible responses**, but the frontend itself is managed separately.

3. **Testing Requirements**
    - **All backend tests must be encapsulated within the domain** to make domains plug-and-play.
    - Backend: **PHPUnit / Pest** for services, models, and controllers.
    - Each generated backend file must include a corresponding test stub.
    - Domain test folder structure:
      ```
      `app/Domains/{DomainName}/tests/UnitTests`
      `app/Domains/{DomainName}/tests/FeatureTests`
      ```

4. **Inertia + Routing**
    - Routes belong in `{Domain}/Http/routes.php`.
    - Register routes via ServiceProvider.
    - The domain should focus on backend logic; frontend components are handled separately.

5. **Service Layer**
    - Service classes must implement `{Domain}ServiceContract.php`.
    - Bind interfaces to implementations in `{Domain}ServiceProvider.php`.
    - Business logic should always reside in the Service, not Controller.

6. **AI Generation Behavior**
    - When generating code or scaffolds:
        - Follow the **DDD backend structure** exactly.
        - Include controllers, models, services, contracts, facades, providers, and **backend unit tests inside the domain**.
        - Do **not** generate Vue components or frontend code inside the domain.

7. **Code Quality**
    - Follow **PSR-12** and **Laravel Style Guide**.
    - Use docblocks for all public methods.
    - Use clear and descriptive naming for all classes and variables.

8. **Domain Encapsulation Goal**
    - Each backend domain must be **fully self-contained**, including:
        - Models
        - Controllers
        - Services & Contracts
        - Routes
        - Unit tests (PHP)
    - Domains should be **plug-and-play**, requiring minimal external changes if moved to another project.

