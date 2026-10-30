# 0002. Arrays with documented shapes instead of models

- **Status:** Accepted
- **Date:** 2026-10-29

## Context

BitGo's responses are large and change often: new fields appear, new wallet types bring new shapes, and some fields only exist for some coins. An SDK can return them as typed model classes, or as the decoded JSON.

Models give autocompletion and type safety, but each one has to be written and kept in step with BitGo. A field BitGo adds is invisible until the SDK adds it, and a field whose type changes can break hydration. Switching between models and arrays later would break every caller, so the choice has to be made before 3.0.

PHPStan and IDEs can check arrays against documented array shapes, which gives most of the safety without the hydration.

## Decision

- **Verbs return BitGo's JSON body, decoded to an array.** There are no model classes.
- **Each verb documents its main fields as an open array shape**, for example `@return array{id: string, address: string, label?: string, ...}`. Fields BitGo always returns are required keys, fields it may leave out end in `?`, and the trailing `...` keeps the shape open for everything else.
- **Shapes are documentation, not checks.** They come from BitGo's API reference and aren't validated at runtime. Where PHPStan needs the shape on a decoded value, the code says so with `/** @var array{…} BitGo's documented shape, not checked at runtime */`.
- **Amounts stay as the strings BitGo sends.** `Amount` is a separate value object you opt into (see `Amount::fromBaseUnits()`), never a field type.
- **The body must be a JSON object.** A 2xx whose body isn't one throws `UnexpectedResponseException` (see [0004](0004-exception-hierarchy.md)), so a verb never returns a list or a scalar where it promises an array.
- **Headers and status** of a successful call are read with `BitGo::lastResponse()`, so no verb needs to change its return type for them.

## Consequences

- A field BitGo adds is in the array on the day it ships, with no SDK release.
- PHPStan flags a direct read of a key the shape doesn't guarantee, so callers read optional fields with `??`.
- A typo in a key outside the documented fields isn't caught, and a shape can drift from what BitGo actually returns. Shapes are updated as part of checking each endpoint against BitGo's reference.
- Helpers that wrap an array, such as typed getters for a transfer, can be added later without breaking anyone, since the array stays the return type.
