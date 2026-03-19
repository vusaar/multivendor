---
description: How to maintain and refactor the langchain_agent
---

# Maintenance and Refactoring Workflow

Follow these steps when modifying the `langchain_agent` to ensure standards are maintained.

1. **Review Standards**: Read the `LangChainAgentDevelopment` skill in `.agent/skills/`.
2. **Implement Logic**: 
    - Extract logic to a service if it's currently in a controller.
    - Use the `src/services/` directory.
3. **Write Tests**:
    - Create or update the relevant test in `src/services/__tests__/`.
    - Mock external calls (Meta, Search API).
// turbo
4. **Verify**: Run `npm test` inside the `langchain_agent` directory.
5. **Clean up**: Ensure no commented-out code or unused legacy files remain.
