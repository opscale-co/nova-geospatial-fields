# Opscale Project Constitution

**Project:** nova-geospatial-fields
**Project Type:** package
**Module Prefix:** geospatial
**Tenant Aware:** no
**Created:** 2026-04-24

> This constitution is the architectural DNA of every Opscale project.
> Claude Code MUST read and comply with it before generating any code, spec, plan, or task.
> It is derived from [opscale-co/strict-rules](https://github.com/opscale-co/strict-rules)
> and supersedes all other instructions. Deviations require explicit documentation.

---

## 0. Project Type

**Type: package**

This project is a standalone Laravel Nova field package published to Packagist. It ships five
purpose-built geospatial Nova fields (Location, Address, Geofence, Area, Route) that render
interactive Leaflet maps inside any Nova Resource that consumes them. It is not a bounded
context of a larger application — any Nova app can pull it in independently.

| Type | Description | Example |
|------|-------------|---------|
| `app` | Complete Laravel Nova application containing multiple modules. Deployed via Vapor or similar. | A multi-module SaaS platform |
| `module` | Single bounded-context package within an app. Handles one business subdomain. | `opscale-co/nova-loan-module` |
| `package` | Standalone project with specific functionality. Can have domain, Nova resources, Actions. Published to Packagist. | `nova-api`, `nova-authorization`, `nova-geospatial-fields` |
| `library` | Pure utility/infrastructure code. No domain, no Nova, no Actions. | `opscale-co/strict-rules`, `opscale-co/actions` |

### What applies per type

| Skill / Agent | app | module | package | library |
|---------------|:---:|:------:|:-------:|:-------:|
| opscale-init | Yes | Yes | Yes | Yes (simplified) |
| opscale-process | Per module | Yes | -- | -- |
| opscale-dbml | Per module | Yes | -- | -- |
| opscale-bpmn | Per module | Yes | -- | -- |
| opscale-domain | Per module | Yes | Yes (direct) | -- |
| opscale-ui | Per module | Yes | Yes (direct) | -- |
| opscale-logic | Per module | Yes | Yes (direct) | -- |
| opscale-outputs | Per module | Yes | Yes (direct) | -- |
| opscale-debug | Yes | Yes | Yes | Optional |
| opscale-test | Yes | Yes | Yes | Yes (adapted) |
| opscale-release | Yes (deploy-app) | Yes (publish-package) | Yes (publish-package) | Yes (publish-package) |
| opscale-ai | Per module | Yes | -- | -- |

### Package-type adaptation for a Nova field library

`nova-geospatial-fields` is a **package** but its surface area is intentionally narrow:

- **No business domain.** The package does not model any business entity of its own. It exposes
  reusable UI primitives. Consumers provide the domain models, migrations, and validation rules.
- **No Opscale Actions.** There is no business logic to encapsulate. Payload normalization
  (GeoJSON in / GeoJSON out) lives inside the Field class itself via a dedicated trait.
- **No Outputs.** The package does not emit notifications, PDFs, webhooks, or events.
- **Heavy front-end surface.** The bulk of the package is Vue 3 + Leaflet. JS/Vue code is held
  to the same quality bar as PHP: `npm run lint` (Duster for JS), no dead code, no silent
  catches, explicit error states in the UI.

The sequence that applies for this package is therefore:

```
1. opscale-init    → scaffold + this constitution
2. Domain layer    → Field classes, service provider, traits (already implemented)
3. UI layer        → Vue components registered with Nova (already implemented)
4. opscale-debug   → (optional) Telescope/Xdebug for the workbench app
5. opscale-test    → Pest + Dusk browser tests + PHPStan + Duster + Rector
6. opscale-release → Semantic Release + SonarQube + GitHub Actions
7. opscale-ai      → Generate installer skill for consumers (independent)
```

Steps that require a business spec (`opscale-process`, `opscale-dbml`, `opscale-bpmn`,
`opscale-logic`, `opscale-outputs`) are **skipped** — this package has no business subdomain.

### Adopting in Existing Projects

1. `opscale-init` detects existing files and uses `--here --force`.
2. Generation agents never silently overwrite — conflicting files are flagged for review.
3. Existing code is the source of truth until the spec catches up.
4. Incremental adoption is valid — not every quality gate needs to be in place on day one.

---

## I. Architectural Philosophy

Opscale software is designed in a strict priority order. When there is a conflict between
levels, the higher level always wins.

**Priority 1 — Business (Information Flow)**
The system exists to model how the business works and move information correctly through it.
For this package, "information" is geospatial data (points, polygons, circles, polylines).
Payload correctness (valid GeoJSON, correct coordinate order `[lng, lat]`, radius in meters,
closed rings) is the single most important invariant — visual polish on the map is secondary
to the data being correctly stored and round-tripped.

**Priority 2 — End Users (Interface)**
End users of this package are two groups:
1. **Laravel developers** consuming the fields — the API (`Location::make()`, `->defaultCenter()`,
   `->geocoder('photon')`) must be discoverable, strongly typed, and documented.
2. **Nova admin-panel users** — the map must load quickly, respond to mouse/touch input,
   degrade gracefully when tiles fail, and expose clear empty-states when the stored value
   is null.

**Priority 3 — Technical Team (Maintainability)**
Code quality, patterns, and architecture conventions exist to serve the team maintaining the
package long term. SOLID, Clean Architecture, and DDD are tools — if a pattern would force
complexity that hurts the developer or admin experience, document the deviation and move on.

**The three design patterns that serve these priorities:**
- **DDD** — the GeoJSON payload is the Value Object for every field; trait composition keeps
  each field focused on its shape (Point / Polygon / LineString / circle-with-radius).
- **Clean Architecture** — the Field class (Interaction) delegates payload handling to a
  dedicated trait (Transformation); the Vue component (Interaction) delegates HTTP calls to
  dedicated services (`geocoder.js`, `routing.js`).
- **SOLID** — fields are closed for modification (payload is normalized in a single trait)
  and open for extension (new fields add a new `$component` and opt into the trait).

---

## II. Design Methodology

Because this package is type `package` with no business subdomain, the full spec-driven
sequence is not applicable. The sequence reduces to:

### Step 1 — Bootstrap → `opscale-init`
**Produces:** This constitution, `.specify/` scaffold, MCP server configuration.
**Gate:** Constitution committed before any implementation.

### Step 2 — Domain classes (already in place)
**Produces:** `src/GeospatialField.php` (abstract base), the 5 concrete Field classes
(`Location`, `Address`, `Geofence`, `Area`, `Route`), `src/Concerns/HandlesGeoJsonPayload.php`,
`src/Concerns/HasMapOptions.php`, `src/FieldServiceProvider.php`, `config/nova-geospatial-fields.php`.
**Constraint:** Every field normalizes its payload through `HandlesGeoJsonPayload` — no
field class duplicates that logic. Every field exposes map configuration through
`HasMapOptions` — no field class invents its own setter name.

### Step 3 — UI layer (already in place)
**Produces:** `resources/js/field.js` (component registration entry), 15 Vue components
(5 fields × 3 views), `resources/js/services/{leaflet,geocoder,routing}.js`, `resources/css/field.css`.
**Constraint:** Every Form component writes its `this.value` as a stringified GeoJSON object
matching exactly the shape the PHP trait expects. Round-trip (load → edit → submit → reload)
must produce an identical payload for unchanged fields.

### Step 4 — Debug Config → `opscale-debug` (optional)
**Covers:** Xdebug + Telescope setup for local workbench development. Never touches production.

### Step 5 — Test Config → `opscale-test`
**Produces:** Pest configuration, Unit tests per field, Dusk browser tests that load the
workbench Nova app and assert every Leaflet map initialises (`.leaflet-container`),
every Detail view renders its shape (marker / svg path / circle), and every empty state
is visible when the backing attribute is `null`.
**Constraint:** One browser test per field per view type (create / detail / update). Index
views get at least one snapshot assertion. No test is allowed to rely on external network
services (Nominatim, OSRM) — those are client-side only and exercised manually.

### Step 6 — Release Config → `opscale-release`
**Produces:** Semantic Release config, SonarQube configuration, GitHub Actions workflows
(`auto-check.yml`, `auto-refactor.yml`, `auto-update.yml`, `publish-package.yml`).

### Step 7 — Installer skill → `opscale-ai` (independent)
**Produces:** A Claude Code skill that ships with the published package and guides the
consumer through installation, config publishing, and backing-column migration snippets.

Steps that do **not** apply to this package: `opscale-process`, `opscale-dbml`, `opscale-bpmn`,
`opscale-logic`, `opscale-outputs`, `opscale-domain` (in the bounded-context sense), `opscale-ui`
(in the Nova-resource sense). Nova Resources in the workbench are test fixtures, not product.

---

## III. Clean Architecture Layers

A Nova field package is a thin vertical slice: it contains only Interaction (Field classes
and Vue components) and Transformation (the payload trait, the geocoder/routing services).
There are no Models, no Observers, no Jobs, no Notifications.

```
src/
├── Concerns/
│   ├── HandlesGeoJsonPayload.php   ← Transformation: request → GeoJSON → model attribute
│   └── HasMapOptions.php           ← Representation: declarative map config exposed as meta
├── GeospatialField.php             ← Abstract Interaction base
├── Location.php                    ← Interaction
├── Address.php                     ← Interaction
├── Geofence.php                    ← Interaction
├── Area.php                        ← Interaction
├── Route.php                       ← Interaction
└── FieldServiceProvider.php        ← Interaction (registers scripts/styles/views/config)

resources/
├── js/
│   ├── field.js                    ← Entry point (Nova.booting registration)
│   ├── components/{field}/{Detail,Form,Index}Field.vue  ← Interaction (Vue)
│   └── services/{leaflet,geocoder,routing}.js           ← Transformation (pure JS)
└── css/field.css

config/nova-geospatial-fields.php   ← Representation (declarative defaults)

workbench/                          ← Test fixtures — NOT shipped to consumers
└── app/{Models,Nova,Providers}     ← Place entity + Nova resource, for Dusk tests only

tests/
├── TestCase.php                    ← Unit test base
├── DuskTestCase.php                ← Browser test base
├── Pest.php
├── Unit/                           ← Field class payload & meta tests
└── Browser/                        ← Dusk tests per field per view
```

**Communication direction:**
- Field class → trait → model attribute (downward, direct method call)
- Vue component → service → external HTTP endpoint (downward, awaited call)
- Form submit → Nova built-in pipeline → `fillAttributeFromRequest` (downward, framework-managed)

No layer calls upward. No layer bypasses another.

---

## IV. DDD Rules

**Not applicable in the bounded-context sense.** This package has no business entities of
its own. However, the GeoJSON payload is treated as a Value Object:

- Every field stores a well-formed GeoJSON object as a JSON column on the consumer's model.
- Coordinate order is always `[longitude, latitude]` — the GeoJSON spec, not the intuitive order.
- Extra field-specific metadata (radius, formatted address, waypoints, distance, duration)
  lives in `properties` — never as a sibling to `type` or `coordinates`.
- Payloads are immutable at the client layer: to change one, replace it; never mutate.

Consumers are responsible for choosing the Eloquent cast (`array`, custom Value Object, or
a spatial type if their DB supports one). The package ships with no assumed cast — it accepts
strings and arrays and returns arrays on resolve.

---

## V. Opscale Actions (Business Logic Units)

**Not applicable.** This package has no business logic. Payload normalization is purely
mechanical and lives in `HandlesGeoJsonPayload`. Geocoding and routing are user-initiated
UI operations — the Vue layer calls an external service directly; there is no server-side
Opscale Action that proxies the call.

---

## VI. Nova Layer Rules

The package itself is part of the Nova Layer — every public class is a Nova Field subclass.
Fields adhere to these rules:

- **One `$component` per field class.** Never share component names across fields.
- **Meta is declarative.** Every public setter writes onto `$meta` via `withMeta()`. No
  setter stores state anywhere else on the field instance.
- **`fillAttributeFromRequest` is overridden via trait, not inline.** All five fields share
  the same implementation — duplicating it inside a field class is forbidden.
- **Empty values clear the attribute.** An empty string or `null` in the request always sets
  the attribute to `null` on the model, never an empty object or empty string.
- **Missing request keys leave the attribute untouched.** When a Nova Resource form does not
  include the field (partial update), the stored value must not be altered.
- **Index view is always a compact summary.** Never render a full map inside a table cell.

Workbench Nova Resources (`workbench/app/Nova/Place.php`, `workbench/app/Nova/User.php`)
are test fixtures. They are not shipped and are excluded from PHPStan's strict DDD rule set
for that reason.

---

## VII. Outputs (Meaningful Delivery)

**Not applicable.** This package emits no notifications, jobs, webhooks, or external calls
from the server side. The Vue layer talks to public OpenStreetMap services (Nominatim,
Photon, OSRM) directly from the browser — those calls are user-initiated and do not flow
through the Laravel app.

---

## VIII. SOLID Rules

All PHP files use `declare(strict_types=1)`. PHPStan runs at level 8 with the four
`opscale-co/strict-rules` rule sets enabled.

**Single Responsibility**
Each Field class declares the shape it stores (`Point`, `Polygon`, `LineString`, `Point`-with-radius)
and any field-specific setters (`->geocoder()`, `->minVertices()`, `->defaultRadius()`,
`->profile()`). Payload normalization is in the trait, not in the field. Meta defaults are in
the `HasMapOptions` trait, not in the field. If a Field class exceeds ~50 lines, it has too
much behavior.

**Open/Closed**
Adding a new geospatial shape means adding a new class that extends `GeospatialField`
and opts into the shared traits. Modifying the trait to add field-specific logic is a
violation — the trait handles only shape-agnostic JSON plumbing.

**Liskov Substitution**
Every concrete Field is substitutable for `GeospatialField` wherever Nova expects a field.
Because the base class overrides `resolve()` and `jsonSerialize()`, subclasses that also
override these must call `parent::` unless the override is a documented full replacement.

**Interface Segregation**
Consumers interact with one Field class at a time. No consumer is forced to understand
routing configuration when they only need a Location pin, nor radius ranges when they only
need a geofence.

**Dependency Inversion**
The Vue components depend on **service modules** (`leaflet.js`, `geocoder.js`, `routing.js`),
not on Leaflet's or fetch's global namespace directly. Swapping the geocoder driver requires
editing only `geocoder.js` — components know only about the abstract `geocode({query, driver,
endpoints})` contract.

---

## IX. Multi-Tenancy

**Tenant Aware: no**

Tenancy is the responsibility of the consuming application. This package never sets, reads,
or filters by a `tenant_id` — it operates on the column the consumer points it at and trusts
the consumer's Resource-level scoping. The package itself carries no shared state that could
leak across tenants.

---

## X. Code Quality Gates

No feature branch merges without passing all of the following:

### PHP
1. ✅ PHPStan level 8 — zero errors across `clean`, `ddd`, `smells`, `solid` rule sets
   (with the per-file ignores declared in `phpstan.neon`).
2. ✅ Duster lint — Pint, PHP-CS-Fixer, PHPCS clean.
3. ✅ Rector — no pending refactors in `--dry-run`.
4. ✅ Pest — Unit tests pass.

### JS / Vue
5. ✅ Duster covers JS/Vue as well — same command, same exit code.
6. ✅ `npm run production` builds without warnings. The `dist/` bundle must be committed
   before any `chore(release)` commit so consumers who install via Composer get the assets
   without running `npm`.

### Browser
7. ✅ Dusk suite passes against the built workbench. Every field's Detail view must render
   its Leaflet container (`.leaflet-container` selector). Every empty-state must be visible
   when the backing attribute is `null`.

### Release
8. ✅ SonarQube quality gate — no new critical or blocker issues.
9. ✅ Semantic Release commit convention on all commits — enforced by commitlint + Husky.

### App- and module-only gates (DO NOT apply here)
- DBML drift check — this package has no DBML.
- BPMN action-task coverage — this package has no BPMN.

---

## XI. Package-Specific Sequence

This package does NOT follow the full spec-driven sequence. It follows:

```
opscale-init      → constitution + scaffold           (DONE)
Implementation    → Field classes + Vue components    (DONE)
opscale-debug     → (optional) Telescope + Xdebug
opscale-test      → Pest + Dusk + PHPStan + Duster + Rector
opscale-release   → Semantic Release + CI/CD + SonarQube
opscale-ai        → Generate installer skill for consumers (independent)
```

The artifacts `spec.md`, `data-model.md`, `process.md`, `plan.md`, `tasks.md`,
`contracts/` do NOT exist for this package. `/speckit.specify`, `/speckit.plan`,
`/speckit.tasks`, `/speckit.implement` are available but are not part of the
package's release workflow — the implementation is already in place and evolves
under Semantic Release once `opscale-test` and `opscale-release` are wired up.

---

## Governance

- This constitution supersedes all other instructions, templates, and conventions.
- Any deviation requires explicit inline documentation with the business or technical reason.
- Amendments must propagate to all dependent `.specify/templates/` files.
- PRs violating any article are blocked until resolved — no exceptions.
