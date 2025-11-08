# Contanta Project Context

## Overview
Contanta is an AI-powered content management and automation platform built with **Laravel 12**, **Vue 3**, and **TailwindCSS**, using **Domain-Driven Design (DDD)** principles.  
The system is modular, scalable, and designed around distinct *domains* and *subdomains*, each encapsulating its own logic, routes, models, and services.

## Project Architecture

### Folder Structure
app/
└── Domains/
├── {DomainName}/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── {Domain}Controller.php
│ │ │ └── {Domain}AdminController.php
│ │ ├── Requests/
│ │ │ └── {FormRequestName}.php
│ │ └── Inputs/
│ │ └── {InputName}.php
│ ├── Constants/
│ │ └── {ConstantFile}.php
│ ├── Models/
│ │ └── {ModelName}.php
│ ├── Services/
│ │ ├── {Domain}Service.php
│ │ ├── {Domain}ServiceContract.php
│ │ └── ...
│ ├── routes.php # Inertia routes
│ ├── {Domain}.php # Facade
│ ├── {Domain}ServiceProvider.php
│ └── {Subdomain}/
│ └── ... (same structure)


### Backend Technologies
- **Laravel 12**
- **Inertia.js (Vue 3 + Laravel Bridge)**
- **PHPUnit / Pest** for unit and feature tests
- **Laravel Service Container** and **Contracts** for dependency inversion

### Frontend Technologies
- **Vue 3 (Composition API)**
- **Inertia.js** for SPA routing
- **TailwindCSS** for design
- **Vitest + Vue Test Utils** for unit tests

### AI & Automation
- Contanta integrates AI-assisted scaffolding commands (e.g., `ai:make domain MenuBuilder`) that auto-generate DDD-compliant code and associated unit tests.

---

## Design & Development Principles

1. **Follow Domain-Driven Design (DDD):**
    - Each domain encapsulates its own logic, models, routes, and services.
    - Cross-domain communication should occur only via service contracts or facades.
    - Keep controllers thin — delegate logic to Services.

2. **Laravel + Inertia Best Practices:**
    - Keep route definitions inside each domain’s `routes.php`.
    - Use Form Requests for validation.
    - Use Inertia responses for all view rendering, passing props from controller to Vue components.

3. **Vue 3 Best Practices:**
    - Use Composition API (`<script setup>`).
    - Keep state in Pinia stores or composables, not in single components.
    - Components should be domain-scoped and reusable.
    - Apply Tailwind utility classes for consistency.

4. **Testing:**
    - Write **unit tests** for Laravel models, services, and controllers.
    - Write **unit and component tests** for Vue components using Vitest.
    - Each generated component or service must include a test stub.

---

## Example Domain: MenuBuilder
A typical `MenuBuilder` domain would contain:
- `MenuBuilderController.php` (Inertia routes)
- `MenuBuilderService.php` (business logic)
- `MenuBuilderServiceContract.php` (interface)
- `Menu.php` (Eloquent model)
- Vue components under `resources/js/Pages/MenuBuilder/`
- Tests under `tests/Unit/Domains/MenuBuilder/`

---

## AI Expectations
Claude should:
- Always scaffold new features respecting DDD domain structure.
- Automatically generate Laravel and Vue test stubs.
- Use Laravel-style naming conventions (PascalCase for classes, snake_case for database).
- Follow PSR-12 and Tailwind styling conventions.


