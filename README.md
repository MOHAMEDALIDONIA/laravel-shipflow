# Laravel Shipping Integration

A flexible and extensible Laravel package for integrating multiple shipping providers like **Aramex**, **SMSA**, **Sally**, and others using a unified interface.

---

## 🚀 Purpose

The goal of this package is to provide a standardized way to handle shipping operations across different providers. It abstracts the complexity of API requests, data validation, and response handling, allowing you to switch or add shipping drivers with minimal effort.

---

## 📦 Installation

1. Install the package via composer:
   ```bash
   composer require mohamedali/laravel-shipping:dev-main
   ```

2. Register the service provider (if not using auto-discovery):
   ```php
   Mohamedali\LaravelShipping\ShippingServiceProvider::class
   ```

3. Publish the configuration and migrations:
   ```bash
   php artisan shipping:publish-config
   php artisan migrate
   ```

---

## 🛠 Commands

The package includes several Artisan commands to streamline development and setup:

### 1. `shipping:publish-config`
Publishes the `shipping.php` configuration file and package migrations.
- **Usage:** `php artisan shipping:publish-config`
- **Options:** `--force` to overwrite existing files.

### 2. `make:shipping-driver {name}`
Generates a new shipping driver and its associated payload class.
- **Usage:** `php artisan make:shipping-driver Fedex`
- **What it does:**
  - Creates a driver class in `app/Shipping/Drivers/`.
  - Creates a payload validation class in `app/Shipping/Payloads/`.
  - Automatically registers the new driver in `config/shipping.php`.

### 3. `make:shipping-payload`
An interactive command to generate a payload class pre-filled with example data for specific providers.
- **Usage:** `php artisan make:shipping-payload`
- **Supported Providers:** Aramex, SMSA, Sally.
- **What it does:** Creates a class with a static `data()` method containing the required API structure, making it easy to test shipments immediately.

---

## 📖 Usage Guide

### 1. Basic Shipment
Resolve the shipping manager and choose your driver. We recommend using a Payload class to structure your data:

```php
use Mohamedali\LaravelShipping\Services\ShippingManager;
use App\Shipping\Payloads\AramexOrderPayload;

$shipping = app(ShippingManager::class)->driver('aramex');

// Prepare payload data using the static data method
$payload = AramexOrderPayload::data($order);

$success = $shipping->processShipment($payload);
```

### 2. Attaching a Model (`for`)
You can link a shipment to any Eloquent model (e.g., Order, User). This automatically records the shipment details in the `delivery_info` table via a morph relationship.

```php
$shipping->for($order)->processShipment($payload);
```

### 3. Success Callback (`onSuccess`)
Execute custom logic immediately after a successful shipment. The callback receives the model instance, the full API response, and the provider name.

```php
$shipping->for($order)
    ->onSuccess(function ($model, $response, $provider) {
        $model->update(['status' => 'shipped']);
        Log::info("Order {$model->id} shipped via {$provider}");
    })
    ->processShipment($payload);
```

### 4. Retrieving Labels
Retrieve the latest successful label URL for a specific model and provider:

```php
$labelUrl = $shipping->getLabel($order);
```

---

## 🏗 Architecture

- **`ShippingManager`**: The entry point that resolves driver instances based on configuration.
- **`BaseShippingService`**: The core abstract class that handles the lifecycle of a shipment (Validation -> Request -> Response Logging -> Callbacks).
- **`Payload` Classes**: Responsible for validating the input data before it's sent to the API.
- **`DeliveryInfo` Model**: Stores the history of shipments, linked to your application's models using polymorphic relationships.
