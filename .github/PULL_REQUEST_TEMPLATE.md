## What changed

<!-- A sentence or two. Link the issue if there is one. -->

## Why

<!-- What problem this solves. -->

## Checklist

- [ ] `cd apps/plugin && composer test` passes
- [ ] `cd apps/plugin && composer lint` passes
- [ ] `bun run typecheck && bun run lint && bun run format:check` pass
- [ ] Docs updated under `apps/docs/content/docs/` if behaviour changed
- [ ] `CHANGELOG.md` updated

## Compatibility

- [ ] No change to REST namespaces, routes or authentication
- [ ] No change to option names, database tables or the `tailsignal_manage` capability

<!-- If either box is unticked, explain the migration path for existing sites and the app. -->
