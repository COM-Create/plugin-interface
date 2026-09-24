# PlentyONE Plugin-Entwicklung – Leitfaden

> **Zielgruppe:** Entwickler und KI-Agenten, die PlentyONE-Plugins entwickeln, debuggen oder
> architektonisch beraten sollen.
> **Stand:** stable7 Interface, PHP 8.0, PlentyONE (Rebranding von plentymarkets)
>
> Hinweis: möglicherweise veraltet — andere COM-Create-Plugins (z.B. MiraklMarketplace) zielen
> aktuell auf `PHP >=7.3 <8.3`, nicht ausschließlich PHP 8.0.

Dieser Leitfaden fasst das allgemeine Plenty-Plugin-Wissen zusammen: `plugin.json`,
ServiceProvider, Routing, Migrations, Crons, Events, Logging, `config.json`, Namespaces,
Modulübersicht, Spezial-Plugins, Übersetzungen und ShopBuilder. Er ist die Quelle, auf die der
Claude-Skill `plenty-docs` verweist, und liegt hier im Repo statt nur lokal in einem
Config-Repo, damit auch Agenten ohne dieses Config-Repo (z.B. Kai/Morpheus) darauf zugreifen
können.

---

## 1. Kernkonzepte

### Plugin Interface vs. REST API

| Aspekt | Plugin Interface | REST API |
|--------|-----------------|----------|
| Zugriff | Innerhalb von Plugins via `pluginApp()` / DI | Extern via HTTP (OAuth 2.0) |
| Sprache | PHP | Beliebig (JSON über HTTP) |
| Namespace | `Plenty\Modules\*` | `/rest/*` Endpoints |
| Doku | Interface Doku (stable7) | OpenAPI 2.0 Spec |
| Use Case | Plugin-Logik, Events, Crons | Externe Integrationen, Apps |

### pluginApp() – Der DI-Container

`pluginApp()` ist das Äquivalent zu Laravels `app()`. Jede Dependency wird darüber aufgelöst:

```php
# Resolve a contract
$orderRepo = pluginApp(OrderRepositoryContract::class);

# With constructor parameters
$instance = pluginApp(MyClass::class, ['param1' => $value]);

# Singleton-Bindung im ServiceProvider
$this->getApplication()->bind(MyContract::class, MyImplementation::class);

# Shared Singleton
$this->getApplication()->singleton(MyService::class);
```

### AuthHelper – Zugriff ohne Authentifizierung

```php
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;

$authHelper = pluginApp(AuthHelper::class);
$address = $authHelper->processUnguarded(function () {
    $repo = pluginApp(AddressRepositoryContract::class);
    return $repo->findAddressById(42);
});
```

---

## 2. Plugin-Projektstruktur

```
PluginName/
├── plugin.json                  # PFLICHT: Plugin-Definition
├── config.json                  # Plugin-Konfiguration (Backend-UI)
├── contentWidgets.json          # ShopBuilder-Widget-Definition
├── marketplace.json             # plentyMarketplace-Metadaten
├── ui.json                      # Backend-UI Entry Points (AngularJS)
├── README.md
│
├── meta/
│   ├── images/                  # Icons (icon_plugin_xs.png, icon_author_xs.png, Previews)
│   └── documents/               # user_guide_de.md, user_guide_en.md, changelog_de.md, changelog_en.md
│
├── resources/
│   ├── css/                     # CSS-Dateien
│   ├── scss/                    # SCSS (wird kompiliert wenn config.json scss:true)
│   ├── js/                      # JavaScript
│   ├── views/                   # Twig-Templates
│   ├── lang/                    # Übersetzungen (de.json, en.json – PFLICHT)
│   ├── images/                  # Bilder für Views
│   └── documents/               # Fonts, PDFs, statische Inhalte
│
├── src/
│   ├── Providers/
│   │   ├── PluginNameServiceProvider.php       # PFLICHT: boot() + register()
│   │   └── PluginNameRouteServiceProvider.php  # Routes (optional)
│   ├── Controllers/
│   ├── Contracts/               # Interfaces/Repositories
│   ├── Models/                  # Datenmodelle (extends Model)
│   ├── Repositories/            # Contract-Implementierungen
│   ├── Services/                # Business-Logik
│   ├── Migrations/              # DB-Tabellen erstellen/ändern
│   ├── Events/                  # Eigene Events
│   ├── Listeners/               # Event-Listener
│   ├── EventProcedures/         # Ereignisaktionen
│   ├── Cron/                    # Cron-Handler
│   ├── Validators/              # Validierungsregeln
│   ├── Helpers/                 # Hilfsklassen
│   └── Containers/              # Template-Container (DataProvider)
│
├── tests/                       # Test-Suite
└── ui/                          # Backend-Views (AngularJS)
```

---

## 3. plugin.json – Plugin-Definition

```json
{
    "name": "MyPlugin",
    "namespace": "MyPlugin",
    "type": "general",
    "version": "1.0.0",
    "description": "Description of my plugin",
    "author": "Author Name",
    "email": "author@example.com",
    "phone": "+49 123 456789",
    "authorIcon": "icon_author_xs.png",
    "pluginIcon": "icon_plugin_xs.png",
    "isClosedSource": false,
    "serviceProvider": "MyPlugin\\Providers\\MyPluginServiceProvider",
    "platform": {
        "php": ">=8.0"
    },
    "require": {
        "IO": "~5.0"
    },
    "dependencies": {
        "guzzlehttp/guzzle": "^7.0"
    },
    "containers": [
        {
            "key": "MyPlugin.Container",
            "name": "My Container",
            "description": "Description"
        }
    ],
    "dataProviders": [
        {
            "key": "MyPlugin\\Containers\\MyDataProvider",
            "name": "My Data",
            "description": "Provides data to template containers"
        }
    ],
    "runOnBuild": [
        "MyPlugin\\Migrations\\CreateMyTable"
    ]
}
```

**Wichtige Felder:**
- `name` / `namespace`: Müssen auf plentyMarketplace einzigartig sein. Namespace = PSR-4 Root.
- `type`: `general`, `template`, `theme`, `shipping`, `payment`, `export`, `backend`
- `version`: Semantic Versioning (MAJOR.MINOR.PATCH)
- `serviceProvider`: Vollqualifizierter Klassenname des Haupt-ServiceProviders
- `require`: Abhängigkeiten zu anderen Plenty-Plugins (Name + Version-Constraint)
- `dependencies`: Packagist-Pakete (nur veröffentlichte Packages erlaubt)
- `runOnBuild`: Migrations-Klassen, die beim Deploy ausgeführt werden
- `containers`: Template-Container die das Plugin bereitstellt
- `dataProviders`: Daten die in Template-Container injiziert werden

---

## 4. ServiceProvider – Einstiegspunkt

### Grundstruktur

```php
<?php

namespace MyPlugin\Providers;

use Plenty\Plugin\ServiceProvider;

/**
 * Main service provider for the MyPlugin plugin.
 *
 * Handles registration of bindings and bootstrapping of services,
 * event listeners, and cron jobs.
 */
class MyPluginServiceProvider extends ServiceProvider
{
    /**
     * Register bindings and sub-providers.
     *
     * Called before boot(). Use for DI bindings and registering
     * other ServiceProviders (e.g. RouteServiceProvider).
     *
     * @return void
     */
    public function register(): void
    {
        # Register RouteServiceProvider
        $this->getApplication()->register(MyPluginRouteServiceProvider::class);

        # Bind contracts to implementations
        $this->getApplication()->bind(
            MyContract::class,
            MyRepository::class
        );
    }

    /**
     * Boot plugin services after all providers are registered.
     *
     * Use for event listeners, cron registration, payment/shipping
     * provider registration, and log reference setup.
     *
     * @return void
     */
    public function boot(): void
    {
        # Boot logic here (events, crons, etc.)
    }
}
```

### register() vs. boot()

| Methode | Zweck | Timing |
|---------|-------|--------|
| `register()` | DI-Bindings, Sub-Provider registrieren | Vor allen anderen Providern |
| `boot()` | Events, Crons, Payment/Shipping registrieren | Nachdem alle Provider registriert sind |

**Regel:** In `register()` niemals andere Services konsumieren – nur binden. In `boot()` kann via DI-Injection auf Services zugegriffen werden.

### boot() mit Dependency Injection

```php
public function boot(
    EventProceduresService $eventProceduresService,
    CronContainer $cronContainer,
    Dispatcher $eventDispatcher
): void {
    # Register event procedure
    $eventProceduresService->registerProcedure(
        'myAction',
        ProcedureEntry::EVENT_TYPE_ORDER,
        ['de' => 'Meine Aktion', 'en' => 'My action'],
        MyProcedure::class . '@handle'
    );

    # Register cron
    $cronContainer->add(
        CronContainer::EVERY_FIFTEEN_MINUTES,
        MyCronHandler::class
    );

    # Listen to events
    $eventDispatcher->listen(
        AfterBasketChanged::class,
        function ($event) {
            # Handle event
        }
    );
}
```

---

## 5. Routing

### RouteServiceProvider

```php
<?php

namespace MyPlugin\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Route service provider for MyPlugin.
 */
class MyPluginRouteServiceProvider extends RouteServiceProvider
{
    /**
     * Map web and API routes.
     *
     * @param Router $router Web router for shop/frontend routes
     * @param ApiRouter $api API router for REST routes (requires OAuth)
     * @return void
     */
    public function map(Router $router, ApiRouter $api): void
    {
        # Web routes (shop frontend)
        $router->get('my-page', 'MyPlugin\Controllers\ContentController@show');
        $router->post('my-page', 'MyPlugin\Controllers\ContentController@store');

        # REST API routes (authenticated via OAuth)
        $api->version(['v1'], ['middleware' => ['oauth']], function ($router) {
            $router->get('myplugin/data', [
                'uses' => 'MyPlugin\Controllers\ApiController@index',
            ]);
            $router->post('myplugin/data', [
                'uses' => 'MyPlugin\Controllers\ApiController@store',
            ]);
        });
    }
}
```

**Router-Methoden:** `get()`, `post()`, `put()`, `patch()`, `delete()`, `options()`, `any()`, `resource()`, `match()`

**Route-Parameter:** `$router->put('todo/{id}', '...')->where('id', '\d+');`

> Hinweis: möglicherweise veraltet — bei manchen Plugins (z.B. MiraklMarketplace) landen
> `ApiRouter`-Routen trotz `version(['v1'], ...)` faktisch unter `/rest/<plugin>/...` ohne
> `/v1/`-Präfix im tatsächlichen Pfad. Vor Verwendung im Einzelfall verifizieren.

### Controller

```php
<?php

namespace MyPlugin\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * API controller for MyPlugin data operations.
 */
class ApiController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param MyService $service
     * @return Response
     */
    public function index(
        Request $request,
        Response $response,
        MyService $service
    ): Response {
        $data = $service->getAll();
        return $response->json($data);
    }
}
```

---

## 6. Datenbank & Migrations

### Model

```php
<?php

namespace MyPlugin\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Data model for the custom table.
 *
 * @property int    $id
 * @property string $name
 * @property int    $userId
 * @property bool   $isActive
 * @property string $createdAt
 */
class MyModel extends Model
{
    /** @var int */
    public $id = 0;

    /** @var string */
    public $name = '';

    /** @var int */
    public $userId = 0;

    /** @var bool */
    public $isActive = false;

    /** @var string */
    public $createdAt = '';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'MyPlugin::MyModel';
    }
}
```

**Erlaubte Property-Typen:** `int`, `string`, `float`, `double`, `boolean`, `array`

**Primary Key:** Default ist `id:int` mit Auto-Increment. Änderbar über:
- `protected $primaryKeyFieldName = 'customId';`
- `protected $primaryKeyFieldType = 'string';`
- `protected $autoIncrementPrimaryKey = false;`

### Migration

```php
<?php

namespace MyPlugin\Migrations;

use MyPlugin\Models\MyModel;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Creates the MyModel database table.
 */
class CreateMyTable
{
    /**
     * @param Migrate $migrate
     * @return void
     */
    public function run(Migrate $migrate): void
    {
        $migrate->createTable(MyModel::class);
    }
}
```

**Migration registrieren** in `plugin.json`:
```json
"runOnBuild": ["MyPlugin\\Migrations\\CreateMyTable"]
```

**Tabelle löschen:** `$migrate->deleteTable(MyModel::class);`

### DataBase Repository

```php
<?php

namespace MyPlugin\Repositories;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use MyPlugin\Models\MyModel;

/**
 * Repository for MyModel CRUD operations.
 */
class MyRepository
{
    /** @var DataBase */
    private DataBase $db;

    /**
     * @param DataBase $db
     */
    public function __construct(DataBase $db)
    {
        $this->db = $db;
    }

    /**
     * @param array<string, mixed> $data
     * @return MyModel
     */
    public function create(array $data): MyModel
    {
        $model = pluginApp(MyModel::class);
        $model->name = $data['name'];
        $model->userId = $data['userId'];
        $model->isActive = true;
        $model->createdAt = date('Y-m-d H:i:s');

        $this->db->save($model);

        return $model;
    }

    /**
     * @param int $id
     * @return MyModel|null
     */
    public function findById(int $id): ?MyModel
    {
        $result = $this->db->find(MyModel::class, $id);
        return $result instanceof MyModel ? $result : null;
    }

    /**
     * @return array<int, MyModel>
     */
    public function getAll(): array
    {
        /** @var array<int, MyModel> $results */
        $results = $this->db->query(MyModel::class)->get();
        return $results;
    }
}
```

---

## 7. Cron-Jobs

### CronHandler implementieren

```php
<?php

namespace MyPlugin\Cron;

use Plenty\Modules\Cron\Contracts\CronHandler as CronHandlerContract;

/**
 * Cron handler for periodic data synchronization.
 */
class SyncCronHandler extends CronHandlerContract
{
    /**
     * Execute the cron job.
     *
     * @return void
     */
    public function handle(): void
    {
        # Sync logic here
    }
}
```

### Registrierung im ServiceProvider

```php
use Plenty\Modules\Cron\Services\CronContainer;

public function boot(CronContainer $cronContainer): void
{
    $cronContainer->add(
        CronContainer::EVERY_FIFTEEN_MINUTES,
        SyncCronHandler::class
    );
}
```

**Verfügbare Intervalle:**
- `CronContainer::EVERY_FIFTEEN_MINUTES` (15)
- `CronContainer::EVERY_TWENTY_MINUTES` (20)
- `CronContainer::HOURLY` (60)
- `CronContainer::DAILY` (3600)

---

## 8. Event-System

### Events lauschen (Dispatcher)

```php
use Plenty\Plugin\Events\Dispatcher;

public function boot(Dispatcher $dispatcher): void
{
    # Listen to order creation
    $dispatcher->listen(
        \Plenty\Modules\Order\Events\OrderCreated::class,
        function ($event) {
            # $event->getOrder()
        }
    );

    # Listen to basket changes
    $dispatcher->listen(
        \Plenty\Modules\Basket\Events\Basket\AfterBasketChanged::class,
        function ($event) {
            # Handle basket change
        }
    );
}
```

### Ereignisaktionen (Event Procedures)

```php
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;

public function boot(EventProceduresService $eps): void
{
    # Register a procedure
    $eps->registerProcedure(
        'myProcedureKey',                           # unique key
        ProcedureEntry::EVENT_TYPE_ORDER,            # event type
        ['de' => 'Meine Aktion', 'en' => 'My Action'], # labels
        MyProcedures::class . '@execute'             # handler
    );

    # Register a filter
    $eps->registerFilter(
        'myFilterKey',
        ProcedureEntry::EVENT_TYPE_ORDER,
        ['de' => 'Mein Filter', 'en' => 'My Filter'],
        MyFilters::class . '@check'
    );
}
```

**Event Types:** `EVENT_TYPE_ORDER`, `EVENT_TYPE_TICKET`

### Procedure-Handler

```php
<?php

namespace MyPlugin\EventProcedures;

use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;

/**
 * Event procedure handlers.
 */
class MyProcedures
{
    /**
     * @param ProcedureEntry $event
     * @return void
     */
    public function execute(ProcedureEntry $event): void
    {
        $order = $event->getOrder();
        # Process order
    }
}
```

---

## 9. Logging

```php
use Plenty\Plugin\Log\Loggable;

class MyController extends Controller
{
    use Loggable;

    public function doSomething(): void
    {
        $this
            ->getLogger('MyPlugin_doSomething')
            ->setReferenceType('orderId')
            ->setReferenceValue(123)
            ->info(
                'MyPlugin::logs.actionPerformed',
                ['detail' => 'value']
            );
    }
}
```

**Log-Level (aufsteigend):** `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`

**ReferenceContainer registrieren** (im ServiceProvider boot):
```php
use Plenty\Log\Services\ReferenceContainer;

$referenceContainer->add(['orderId' => 'orderId']);
```

**Logs ansehen:** Backend → Data exchange → Log

---

## 10. Plugin-Konfiguration (config.json)

```json
[
    {
        "tab": "General",
        "key": "global.mode",
        "label": "Operating mode",
        "type": "dropdown",
        "possibleValues": {
            "0": "Test",
            "1": "Live"
        },
        "default": "0"
    },
    {
        "tab": "General",
        "key": "global.apiKey",
        "label": "API Key",
        "type": "text",
        "default": ""
    },
    {
        "tab": "General",
        "key": "global.isActive",
        "label": "Plugin active",
        "type": "checkbox",
        "default": false
    }
]
```

**Config in PHP lesen:**
```php
use Plenty\Plugin\ConfigRepository;

$configRepo = pluginApp(ConfigRepository::class);
$mode = $configRepo->get('MyPlugin.global.mode', '0');
```

**Config in Twig lesen:**
```twig
{% set mode = config("MyPlugin.global.mode") %}
```

**Verfügbare Typen:** `text`, `password`, `textarea`, `checkbox`, `dropdown`, `multiselect`, `date`, `file`, `number`, `color`, `category_picker`

---

## 11. Namespace-Konventionen

Alle Plenty-Interfaces folgen dem Schema:
```
Plenty\Modules\{Modul}\{Submodul}\{Contracts|Models|Services|Events|Helpers}
```

Beispiele:
- `Plenty\Modules\Order\Contracts\OrderRepositoryContract`
- `Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract`
- `Plenty\Modules\Account\Contact\Models\Contact`
- `Plenty\Modules\Payment\Contracts\PaymentRepositoryContract`
- `Plenty\Modules\Cron\Services\CronContainer`

### Typische Repository-Methoden

Die meisten Repository-Contracts bieten:
- `findById(int $id)` / `get(int $id)` – Einzelnen Datensatz laden
- `create(array $data)` – Neuen Datensatz anlegen
- `update(array $data, int $id)` – Datensatz aktualisieren
- `delete(int $id)` – Datensatz löschen
- `search()` / `getAll()` / `list()` – Paginierte Listen
- `clearCriteria()` – Filter zurücksetzen

---

## 12. Thematische Modulübersicht

### E-Commerce Kern
- **Artikel & Varianten:** `Item`, `Pim`, `ItemSet`, `Property`, `PriceCalculation`
- **Kategorien:** `Category`
- **Bestand & Lager:** `Stock`, `StockManagement`, `Warehouse`
- **Aufträge:** `Order`
- **Zahlungen:** `Payment`
- **Versand & Fulfillment:** `Fulfillment`
- **Warenkorb & Checkout:** `Basket`, `Frontend`
- **Dokumente:** `Document`

### Kunden & Kontakte
- **Kontakte & Adressen:** `Account`
- **Bewertungen:** `Feedback`
- **Ticketsystem:** `Ticket`
- **Nachrichten:** `Messenger`

### Marktplätze
- **Marktplatz-Basis:** `Market`
- **Amazon:** `Amazon`, `AmazonFlatfiles`
- **eBay:** `Market` (eBay-Submodule), `Listing`
- **Kaufland:** `Kaufland`
- **Katalog-Export:** `Catalog`

### System & Infrastruktur
- **Auth:** `Authentication`, `Authorization`
- **User & Rechte:** `User`, `Authorization`
- **Plugin-Dev:** `Plugin`, `PluginMultilingualism`, `Template`
- **Cron:** `Cron`
- **Datenimport/-export:** `DataExchange`, `ElasticSync`, `Data`
- **Ereignisaktionen:** `EventProcedures`, `Flow`
- **E-Mail:** `Mail`
- **Cloud & Storage:** `Cloud`
- **Logging:** `AuditLog`, `DeleteLog`
- **Config:** `System`

### Frontend / Webshop
- **Frontend-Services:** `Frontend`
- **ShopBuilder:** `ShopBuilder`, `ContentBuilder`
- **Webshop-Config:** `Webshop`
- **Content-Cache:** `ContentCache`
- **Blog:** `Blog`
- **Facetten-Suche:** `Facet`

**Modul-Details abrufen:** `https://developers.plentymarkets.com/en-gb/interface/stable7/{Modulname}.html`

---

## 13. Erlaubte PHP-Funktionen (Whitelist)

PlentyONE whitelistet native PHP-Funktionen. Nicht gelistete Funktionen führen zu Build-Fehlern.

Statt einer eigenen, potenziell veralteten Kopie der Whitelist gilt hier ausschließlich:

- **Offizielle Whitelist (Source of Truth):**
  [`NativePHPFunctionsAndClasses.html`](https://developers.plentymarkets.com/en-gb/interface/stable7/NativePHPFunctionsAndClasses.html)
- **Konkret bestätigte Build-Verstöße + Ersatz** (Funktion, Fehlermeldung, Alternative):
  [`phpstan-blacklist/README.md`](../phpstan-blacklist/README.md) in diesem Repo. Ein PHP-Repo
  mit `phpstan-stubs` als Dependency bekommt diese Blacklist zusätzlich lokal unter
  `vendor/plentymarkets/plugin-interface/phpstan-blacklist/README.md` und geprüft über
  `vendor/bin/phpstan` (Identifier `plenty.*`).

Im Zweifel: gegen die offizielle Whitelist prüfen, nicht gegen Erfahrungswerte. Reflection und
dynamische Aufrufe (`class_exists()`, `ReflectionClass`, `$fn()`, `$obj->$method()`) sind
zusätzlich zur I/O-Sperre komplett blockiert — Details siehe `phpstan-blacklist/README.md`.

---

## 14. Spezial-Plugins

### Payment-Plugin (Kurzreferenz)

```php
# ServiceProvider boot():
$payContainer->register(
    'PluginKey::PaymentKey',
    PaymentMethod::class,
    [AfterBasketChanged::class, AfterBasketCreate::class, ...]
);
```

Events: `GetPaymentMethodContent`, `ExecutePayment` → im Dispatcher registrieren.
Refunds: Via EventProcedures registrieren.
Doku: `https://developers.plentymarkets.com/en-gb/developers/main/payment-plugins/overview.html`

### Shipping-Plugin (Kurzreferenz)

```php
# ServiceProvider boot():
$shippingService->registerShippingProvider(
    'PluginName',
    ['de' => 'Mein Versand', 'en' => 'My Shipping'],
    [
        'MyPlugin\\Controllers\\ShippingController@registerShipments',
        'MyPlugin\\Controllers\\ShippingController@deleteShipments',
    ]
);
```

Doku: `https://developers.plentymarkets.com/en-gb/developers/main/shipping-plugins/how-to-shipping-plugin.html`

### Katalog-Export-Plugin (Kurzreferenz)

Template-Provider registrieren, dann Export via REST-Route oder Cron triggern.
Verwendet `ApiRouter` mit OAuth-Middleware für REST-Endpoints.
Doku: `https://developers.plentymarkets.com/en-gb/developers/main/export-plugins/basic-usage.html`

---

## 15. Übersetzungen (Mehrsprachigkeit)

**Dateistruktur:**
```
resources/lang/
├── de.json
└── en.json
```

**Format:**
```json
{
    "myKey": "Mein Text",
    "greeting": "Hallo :name"
}
```

**In PHP:**
```php
use Plenty\Plugin\Translation\Translator;

$translator = pluginApp(Translator::class);
$text = $translator->trans('MyPlugin::default.myKey');
$greeting = $translator->trans('MyPlugin::default.greeting', ['name' => 'Max']);
```

**In Twig:**
```twig
{{ trans("MyPlugin::default.myKey") }}
```

---

## 16. Template-System & ShopBuilder

### Template-Override (Theme-Plugin)

```php
use Plenty\Modules\Webshop\Template\Providers\TemplateServiceProvider;

class MyThemeServiceProvider extends TemplateServiceProvider
{
    public function boot(Dispatcher $eventDispatcher): void
    {
        # Override a Twig template
        $this->overrideTemplate(
            'Ceres::Item.SingleItemWrapper',
            'MyTheme::Item.SingleItemWrapper'
        );
    }
}
```

### Template-Events (IO)

```php
# Override a page template
$eventDispatcher->listen('IO.tpl.basket', function (TemplateContainer $container) {
    $container->setTemplate('MyTheme::content.Basket');
    return false;
}, 0);

# Override a partial
$eventDispatcher->listen('IO.init.templates', function (Partial $partial) {
    $partial->set('footer', 'MyTheme::content.Footer');
}, 0);

# Add scripts
$eventDispatcher->listen('IO.Resources.Import', function (ResourceContainer $container) {
    $container->addScriptTemplate('MyTheme::content.MyScript');
}, 0);

# Add styles
$eventDispatcher->listen('IO.Resources.Import', function (ResourceContainer $container) {
    $container->addStyleTemplate('MyTheme::content.MyStyle');
}, 0);

# Extend context
$eventDispatcher->listen('IO.ctx.basket', function (TemplateContainer $container) {
    $container->setContext(MyContext::class);
    return false;
}, 0);
```

### Twig-Basics

```twig
{# Plugin-Pfad zu Resources #}
{{ plugin_path("MyPlugin") }}/images/logo.png

{# Config-Wert lesen #}
{% set apiKey = config("MyPlugin.global.apiKey") %}

{# Übersetzung #}
{{ trans("MyPlugin::default.title") }}
```

---

## 17. Verwandte Dokumentationen

| Ressource | URL |
|-----------|-----|
| Plugin Interface (stable7) | `https://developers.plentymarkets.com/en-gb/interface/stable7/index.html` |
| PlentyONE REST API (Swagger) | `https://developers.plentymarkets.com/en-gb/plentymarkets-rest-api/index.html` |
| Developer Guides & Tutorials | `https://developers.plentymarkets.com/en-gb/developers/main/plugin-architecture.html` |
| GitHub Plugin Interface | `https://github.com/plentymarkets/plugin-interface` |
| GitHub API Doc (OpenAPI 2.0) | `https://github.com/plentymarkets/api-doc` |
| Ceres Plugin (Shop-Template) | `https://developers.plentymarkets.com/en-gb/plugin-ceres/5.0.0/index.html` |
| IO Plugin (Shop-Logik) | `https://developers.plentymarkets.com/en-gb/plugin-io/5.0.0/index.html` |
| Erlaubte PHP-Funktionen | `https://developers.plentymarkets.com/en-gb/interface/stable7/NativePHPFunctionsAndClasses.html` |
| Aktive Modulversionen | `https://developers.plentymarkets.com/en-gb/plentyModules/stable7/versions.html` |
| Packagist Plugin Interface | `https://packagist.org/packages/plentymarkets/plugin-interface` |

---

## 18. Hinweise für KI-Agenten

1. **Modul-Details abrufen:** Fetch `https://developers.plentymarkets.com/en-gb/interface/stable7/{Modulname}.html` für Contract-Signaturen und Model-Felder.
2. **Contracts vs. Models:** Contracts = verfügbare Methoden (Interface). Models = Datenstruktur (Properties, Relationen).
3. **REST API ≠ Plugin Interface:** Manche Funktionalitäten gibt es in beiden, aber Methoden und Parameter unterscheiden sich.
4. **Versionierung:** `stable7` = stabil. `early` = neuere Features (potenziell instabil). `beta7` = experimentell.
5. **Erlaubte PHP-Funktionen prüfen:** Vor Verwendung nativer PHP-Funktionen immer gegen die offizielle Whitelist (siehe Abschnitt 13) validieren — nicht gegen Erfahrungswerte oder ältere Notizen. `curl_*` ist laut offizieller Whitelist erlaubt; Guzzle bleibt trotzdem der bevorzugte Stil.
6. **phpstan Level 9:** Alle generierten Code-Beispiele sollten phpstan-Level-9-kompatibel sein. Achte auf strikte Typisierung, Return-Types, Null-Checks und generische Array-Typen.
7. **pluginApp() statt new:** Niemals `new ClassName()` für Plenty-Klassen verwenden – immer `pluginApp()`.
8. **Deploy-Zyklus:** Codeänderungen erfordern einen Plugin-Build/Deploy. Resources (CSS/JS/Views) können schneller via plentyDevTool gepusht werden.
9. **PHP 8.0:** Plugins liefen historisch auf PHP 8.0.
   > Hinweis: möglicherweise veraltet — einzelne Plugins zielen inzwischen auf einen breiteren
   > Bereich (z.B. `>=7.3 <8.3`). Named Arguments, Match-Expressions, Nullsafe-Operator etc.
   > nur nutzen, wenn die `platform.php`-Constraint des jeweiligen Plugins das zulässt, und
   > stets gegen die Whitelist prüfen.
10. **Translations PFLICHT:** Deutsch und Englisch sind Pflichtsprachen für Plugin-Texte.
