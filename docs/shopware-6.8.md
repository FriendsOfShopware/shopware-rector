# Shopware 6.8 migration coverage

Enable the set when migrating an extension from Shopware 6.7 to 6.8:

```php
use Frosh\Rector\Set\ShopwareSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([ShopwareSetList::SHOPWARE_6_8_0]);
```

Run Rector with your extension's dependencies available for type analysis, review the diff, and run your extension tests.
The rules match Shopware types, so unrelated methods with the same name are preserved.
This set does not include earlier Shopware sets or dependency upgrade sets.

The checklist below was reviewed against the parent Shopware checkout's `UPGRADE-6.8.md` and corresponding PHP implementations on 2026-10-27.
The file in that checkout is named `UPGRADE-6.8.md`, rather than `RELEASE_INFO-6.8.md`.
The guide contains forward-looking changes; rules also recognize types whose inheritance changes in 6.8.

## Early adoption on Shopware 6.7

`ShopwareSetList::SHOPWARE_6_7_0` also enables these migrations:

| Rule | Compatibility with Shopware 6.7.0.0 |
| --- | --- |
| `ReflectionHelperToNativeReflectionRector` | The [original helper](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Framework/Test/TestCaseHelper/ReflectionHelper.php) wraps native reflection, which supports these operations on the required PHP 8.2 baseline. |
| `FilterablePriceDefinitionRector` | [FilterableInterface](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Checkout/Cart/Price/Struct/FilterableInterface.php) already declares `getFilter(): ?Rule`; matching custom price definitions can implement it now. |

Both sets import the same registration for these two rules.
Their existing `Rule\v68` class names remain available.
Adding `FilterableInterface` also makes a custom price definition eligible for the interface checks already used by 6.7 promotion discount packaging; test the affected promotions.

The compatibility baseline is **6.7.0.0**, rather than the latest 6.7 patch.
The other 6.8 rules are not enabled wholesale in the 6.7 set:

| Candidate | Reason to keep it in the 6.8 set |
| --- | --- |
| Result collection delegation | In [6.7.0.0](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Framework/DataAbstractionLayer/Search/EntitySearchResult.php), `filter()` and `slice()` return result wrappers with metadata, and mutations can affect separate wrapper and entity-collection storage. |
| Email constraint context | [The 6.7 constructor](https://github.com/shopware/shopware/blob/v6.7.0.0/src/Core/Checkout/Customer/Validation/Constraint/CustomerEmailUnique.php) accepts separate framework and sales-channel contexts; replacing one with the other can change the context returned. |
| Constraint constructors, exception factories, configuration getters and renamed maintenance APIs | The replacement signatures or methods are missing from 6.7.0.0. |
| Checkout permissions, moved classes, product-stream and number-range abstractions | Their replacement classes are missing from 6.7.0.0. |
| Entity-definition constructor removal | The 6.7 parent constructor still initializes the definition's class name. |
| Mixed helper/type rules | Individual replacements may exist earlier, but the complete rules also reference later APIs, such as `ArrayValues` or the abstract product-stream builder. |
| Removed cache states, profile labels, primary order associations and remote-thumbnail assignments | These depend on changed behavior or data contracts; the presence of a replacement symbol alone does not establish 6.7 compatibility. |

## Automated PHP changes

| Upgrade change | Transformation and boundaries |
| --- | --- |
| Configuration service getters | `getConfiguration()` → `getSystemConfigDefinition()`; `getResolvedConfiguration()` → `getResolvedSystemConfigDefinition()`. |
| Maintenance allowlist naming | Rename sales-channel getters, setters, property accesses, and the two request/cache header constants. |
| Theme lifecycle compilation state | Move the constant to `AbstractAppLifecycle`. |
| Moved DAL exception | Rename `Dbal\Exception\UnmappedFieldException` to the DAL exception namespace. |
| Administration notification classes | Move the collection, definition and entity to `Core\Framework\Notification`. |
| Elasticsearch search configuration alias | Replace it with the DAL `SearchConfigLoader`. |
| Console style | Replace `ShopwareStyle` with `SymfonyStyle`. |
| Checkout permissions | Move the existing persister, delivery, promotion and product processor constants to `CheckoutPermissions`, including the correct delivery-price permission. |
| Document prices | `ZugferdDocument::getPrice()` → `getPriceWithFallback()`. |
| Profile labels and manufacturer links | Read profile technical names instead of labels, and read manufacturer links through `getTranslation('link')`. |
| Event context getters | Replace `getNullableContext()` with `getContext()` on all nine events listed in the guide. |
| Flow criteria event | Rename the event class, replace `getCriteria()`/`getEntityName()` with properties, and rename literal `flow.storer.<entity>.criteria.event` event names. |
| Result collection API | Delegate collection operations, iteration, `yield from`, unpacking and iterator/count functions through `getEntities()` for `EntitySearchResult`, `ProductListingResult` and `ProductReviewResult`, including nullsafe calls. |
| Product stream criteria | Replace inline `addFilter(...buildFilters(...))` and adjacent assignment/add-filter statements; preserve extra filters and variables referenced later. |
| Product stream and number range interfaces | Replace native type declarations and convert interface implementations without an existing parent to the abstract service. |
| OAuth implementation types | Replace native parameter, property and return types with the corresponding League interfaces, including nullable, union and intersection types. |
| Custom constraint options | Convert literal options arrays for all six constraints to named arguments; remove an explicit null options placeholder and name the remaining positional arguments. |
| Constraint accessors | Replace password constraint `getContext()`, email constraint framework-context access, and reads of properties that become protected with public getters. |
| Removed exception constructors | Use the DAL, system config, number range, theme and storefront domain factories; translate changed argument names. |
| Cart load, thumbnail, theme compilation and rate-limiter overrides | Add the new optional parameters and forward them in matching parent/decorated calls. |
| Nullable document references | Widen the two document entity setter parameter types from `string` to `?string`. |
| Translation-loader override | Rename `pluginTranslationExists()` to `pluginTranslationExistsForLocale()`, add `$locale`, and forward it in matching calls. |
| Cart persister and increment-storage decorators | Add the new abstract method as a delegation when `getDecorated()` directly returns a stored instance. |
| Domain-loader decorators | Convert a pure forwarding `load()` implementation to `loadDomains(): DomainCollection`. |
| Entity definition constructor | Remove no-argument `parent::__construct()` statements in direct subclasses. |
| Rule container serialization | Rename a standalone `__wakeup()` to `__unserialize(array $data): void`, with the parent call first. |
| Filterable price definitions | Add `FilterableInterface` to price definitions declaring a public `getFilter(): ?Rule` or `Rule` method. |
| Reflection test helper | Replace its four static helpers with native reflection; avoid evaluating an object-producing expression twice. |
| Array assertion test trait | Replace `$this->assertValues()` with `ArrayValues::assertValues()` and remove the trait when all recognized uses are migrated. |
| Promotion error helpers | Replace standalone calls in classes using `PromotionCartInformationTrait` with `Cart::addErrors(new …Error(...))`. |
| Primary order associations | Replace `getDeliveries()->first()` and `getTransactions()->last()`, including nullsafe variants, with the primary association getters. |
| Remote thumbnail metadata | Move standalone writes to `mediaPath` and `mediaUpdatedAt` into `mediaEntity->assign()`, which supports both full and partial entities. |
| API client constructor | Name arguments to preserve evaluation order across the position change and make the former implicit `confidential: true` explicit, including a null-coalescing fallback for nullable values. |
| Homepage hreflang parameter | Set `homepage: true` for literal `frontend.home.page` constructions whose old fallback provided it. |
| Removed constructor parameters | Remove literal cart `isRecalculation: false`, product export `total`, and cache attribute `states`; use named arguments to avoid positional drift on subsequent runs. |
| Route cache states | Remove literal `states` entries under `PlatformRequest::ATTRIBUTE_HTTP_CACHE` while preserving other defaults. |
| Script response cache TTL | Replace PHP `ResponseCacheConfiguration::maxAge()` with `sharedMaxAge()`, as specified in the App System section and the deprecated implementation. |

The exception rule covers `InvalidPriceFieldTypeException`, `InvalidDomainException`, `InvalidKeyException`, `InvalidSettingValueException`, `IncrementStorageNotFoundException`, `NoConfigurationException`, `ThemeAssignmentException`, `InvalidThemeBundleException`, and `MediaValidatorMissingException`.
For invalid-setting values, both type names must be supplied and known to be strings because the new factory requires them.
The number range factory is in `Shopware\Core\System\NumberRange\NumberRangeException`; one namespace in the upgrade guide is outdated.

## Partial migrations requiring review

| Change | Remaining work |
| --- | --- |
| Event constructors requiring `Context` | Supply the correct operation context; it cannot be inferred from an arbitrary scope. |
| Number range abstraction | Implement allocation, persisted-number-range preview and decoration contracts; a type-based preview cannot be converted without a number range ID. |
| Product stream abstraction | Implement `enrichCriteria()` in custom builders and preserve grouping behavior; ambiguous calls receive one TODO comment. |
| Result wrappers | Update collection/search-result type assumptions, readonly-property writes, removed setters, custom `createNew()`, and review-result construction with domain-specific values. |
| Override migrations | Check method bodies against the new semantics; variadics, parameter collisions and already customized signatures are skipped. |
| Renamed price/context calls | Custom `getPrice()` and `getNullableContext()` declarations need signature/body changes; the rule only renames calls because their replacement contracts differ. |
| Translation loader | Scope custom checks to the supplied locale; external call sites require the actual locale. |
| Domain loaders | Convert array processing to `DomainCollection`/`DomainStruct`; only pure forwarding decorators are rewritten. |
| Cart persister/increment storage | Implement real storage behavior in non-decorating implementations; throwing `getDecorated()` methods are preserved. |
| Constraint options | Dynamic arrays, unpacking, unknown options, mixed legacy/explicit arguments and conflicting aliases need review. |
| Exception catches and types | Domain exceptions cover more error codes than the old subclasses; adjust catches, `instanceof` checks and error-code filtering deliberately. |
| Cart recalculation | Select the applicable granular permission for each use; `isRecalculation()` and a true/dynamic constructor argument have no universal equivalent. |
| Primary order associations | Load the new associations in criteria and supply primary IDs in writes. |
| Maintenance allowlist | Migrate DAL field strings, payloads, database columns and external API consumers. |
| OAuth | Replace direct instantiation, inheritance, implementation-specific calls, service wiring and PHPDoc types as appropriate. |
| Helpers and serialization | Review trait aliases, inherited/dynamic calls, side effects, existing `__unserialize()` implementations and local-variable collisions. |
| Remote thumbnails | Review reads and compound/embedded writes, especially the `updatedAt`/`createdAt` fallback. |
| Constructors | Unpacked arguments and removed expressions with possible side effects are preserved; dynamic homepage route names need review. |

`CartBehaviorIsRecalculationRector` remains individually available for existing users, but is deliberately excluded from the 6.8 set.
Its single `SKIP_CART_PERSISTENCE` substitution cannot represent every removed recalculation use.

## Changes requiring application-specific migration

These PHP-related guide entries cannot be completed by a local, behavior-preserving syntax rewrite:

| Guide entries | Reason |
| --- | --- |
| `RegulationPrice`, `PromotionItemBuilder::buildPlaceholderItem()` | The replacements require a unit price or a line-item factory and sales-channel context. |
| Template config PHP helpers, theme configuration methods | The replacement services must be injected and callers must choose the required configuration API. |
| Scheduled task execution and removed status/rescheduling hooks | Manual construction needs an executor/clock; custom scheduling must implement the new scheduling contract. |
| Payment/shipping `filterByActiveRules()` | Inject `RuleIdMatcher` and decide how the filtered collection is consumed. |
| `Request::get()` and `RequestParamHelper` | Choose attributes, query, or form data; the old precedence cannot identify the intended source. |
| `SeoResolverData::get()` | Process all matching entities rather than assuming one result. |
| `AbstractCaptcha`, honeypot validation | Convert validity and violation handling to the new validation contract. |
| `DomainRuleStruct` string argument | Parse directives with the event dispatcher and correct context. |
| `MediaUploadService::validateExternalUrl()` | Supply the instance service instead of a static call. |
| `SalesChannelContextRestorer::restoreByOrder()` | Load the order and required associations, assemble context and decide whether to reevaluate rules. |
| `CmsPageLoadedEvent` | Supply a `CmsPageCollection` and adapt custom constructor/getter implementations to the collection contract. |
| `#[Serialized]` | Select a real serializer; the removed default does not work. |
| Landing-page slot config | Supply the intended array instead of null. |
| Product/line-item states and `LineItemProductStatesRule` | Migrate the data model, immutable product type and rule configuration, including variants. |
| Manufacturer/profile writes and translations, theme labels and help texts | Migrate stored data, translation associations and administration snippets; the read-only getter replacements do not migrate writes. |
| `SystemConfigService` silent writes and created/updated-by write scopes | Decide whether the old invalidation/scoping behavior is needed. |
| Removed system-config tracing, task registration message, database helper methods | Rework tracing, synchronous task registration or schema inspection around the owning service. |
| Final/internal classes (`Feature`, `EventDataCollection`, theme lifecycle, snippet validator, OAuth, notification controller, asset service) | Remove unsupported inheritance or dependencies; replacing them requires knowledge of the extension's design. |
| Removed cache events/listeners, cache-state cookies/constants, rule-area constants, reverse-proxy compiler pass | Review subscribers, service definitions and caching requirements; deleting arbitrary methods or subscriptions could remove unrelated behavior. |
| Removed FK handlers, unused exceptions, `ErrorRoute`, `TemplateGroup`, `TaskScheduler::getNextExecutionTime()`, `NavigationRoute::buildName()` | No equivalent public operation, or existing behavior is obsolete; remove/rework affected usage. |
| Product export `getTotal()`, partial listing fields | Choose pagination/resume logic or the fields to exclude; totals and allowlists have no direct replacement. |
| Core script `response.render()` | Select the facade for the hook and the correct JSON, redirect or Storefront response. |
| Cart deletion/deferred errors, deferred flows, customer-group contexts, mail payload data, document nullability, payment finalization, automatic promotions | Update business-process assumptions and test the extension's behavior. |
| Multiple SQL statements, state-machine destinations, price/quantity constraints, tax/shipping calculation, URL encoding, default/clone fields, language fetching, logout tokens | Review queries, data and behavior; no universally correct PHP expression replaces these changes. |

The PHP Rector set does not parse Twig, JavaScript, Vue, SCSS, XML, YAML, JSON, SQL or deployment shell scripts.
Consequently the guide's Administration/Storefront template and component changes, app-script macros, inline manifest custom fields, XML service/route/package conversion, snippet and theme translations, HTTP/API contract changes, CLI flags/aliases, cache/filesystem/search configuration and infrastructure/dependency requirements need their respective migration tools or manual changes.
`NotFilter`'s widened argument type is backward compatible and needs no rule.

## Verification

Fixtures cover changed and unchanged examples, named and unpacked arguments, nullsafe access, unrelated types, side effects, existing signatures, and complete-set interactions.
Rector's fixture runner also checks unchanged output on a second pass.
The test stubs model listing and review results as independent wrappers, matching their 6.8 contracts.
