---
name: LangChainAgentDevelopment
description: Standards and patterns for developing the langchain_agent project, focusing on service-oriented architecture and testability.
---

# LangChain Agent Development Standards

This skill defines the architectural and testing standards for the `langchain_agent` Node.js project. All future agents MUST adhere to these patterns to maintain a high-quality, testable codebase.

## Core Principles

1. **Service-Oriented Architecture (SOA)**:
    - Controllers should be "slim" and only handle request validation and response formatting.
    - All business logic, third-party API calls (e.g., Meta/WhatsApp), and database orchestration MUST reside in dedicated Service classes.
    - Services should be located in `src/services/`.

2. **Test-Driven Development (TDD) / Testability**:
    - Every new service or significant business logic change MUST include corresponding unit tests.
    - Tests are located in `src/services/__tests__/`.
    - Use **Jest** for testing. Mock external dependencies (fetch, databases, other services) to ensure tests are fast and isolated.

3. **Dependency Injection Pattern**:
    - Services should ideally be structured to allow for easy mocking. Avoid deep inheritance; favor composition.
    - If a service depends on an environment variable or a URL, allow it to be passed via constructor or a setter for easier testing.

4. **Database & Migrations**:
    - When performing cleanup or schema changes that involve deleting models, use raw SQL (`DB::statement` in Laravel or `db.query` in Node) to avoid dependency errors on deleted classes.

## Project Structure Standards

- `src/controllers/`: Route handlers.
- `src/services/`: Business logic and external integrations.
- `src/services/__tests__/`: Unit tests for services.
- `src/routes/`: Express route definitions.
- `src/config/`: Database and LLM configurations.

## Common Workflows

### Adding a New Feature
1. Create a new Service in `src/services/`.
2. Write unit tests in `src/services/__tests__/`.
3. Ensure tests pass with `npm test`.
4. Update/Create the controller to use the new service.

### Debugging
- Use `npm test` as the first line of defense to verify logic.
- Check `agent_debug.log` and standard output for `[SERVICE NAME]` prefixed logs.
