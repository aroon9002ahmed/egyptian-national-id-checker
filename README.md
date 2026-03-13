# Egyptian National ID

A PHP and Laravel package to parse and validate Egyptian National IDs (14 digits).

## Installation

You can install the package via Composer:

```bash
composer require aroon/egyptian-national-id-checker
```

_(Note: Ensure you are requiring the local path in your project if you haven't published it to Packagist)._

## Features

- **Sanitization**: Automatically cleans input by converting Arabic/Hindi numerals (٠-٩) to English numerals and stripping spaces or dashes.
- **Validation**: Strict validation of the 14-digit format, century, birth date, governorate code, and check digit.
- **Data extraction**: Easily extract birth date, gender, governorate, and age.

## Usage in PHP

```php
use Aroon\EgyptianNationalId\EgyptianNationalId;

// 1. Basic Parsing and Validation
$id = new EgyptianNationalId('٢٩٠-٠١٠١ ١٢٣ ٤٥٦٧');

if ($id->isValid()) {
    $id->getBirthYear();      // 1990
    $id->getGender();         // "female"
    $id->isAdult();           // true
    $id->getGovernorateName();// "Cairo"
    $data = $id->toArray();
}
```

### 🧠 Static Safe Helpers

Need a quick answer without crashing or handling invalid objects? Use the static safe helpers. They return `false` gracefully if the ID is malformed.

```php
EgyptianNationalId::isValidId('29001011234567');   // true
EgyptianNationalId::checkIsMale('29001011234567'); // false
EgyptianNationalId::checkIsAdult('29001011234567');// true
```

### 🎲 ID Generator (For Testing/Factories)

Generate 100% mathematically correct National IDs matching precise criteria, great for Unit Tests:

```php
// Generate a completely random valid ID
$randomId = EgyptianNationalId::generate();

// Customize generation (e.g. Female, born in 1990, from Cairo)
$specificId = EgyptianNationalId::generate([
    'gender' => 'female',
    'year' => 1990,
    'governorate' => '01' // Cairo
]);
```

### 🎛️ Data Engine (Collections and Big Data)

To analyze arrays or databases packed with thousands of IDs, use `EgyptianNationalIdEngine`:

```php
use Aroon\EgyptianNationalId\EgyptianNationalIdEngine;

$dataset = ['29001011234567', '30205051234567', 'invalid_id'];

$engine = EgyptianNationalIdEngine::make($dataset);

// 1. Filter out IDs easily
$onlyMales = $engine->filter(fn($id) => $id->isMale());

// 2. Extract quick demographics & statistics
$stats = $engine->stats();
/*
{
    "total": 2,
    "males": 1,
    "females": 1,
    "adults": 2,
    "governorates": { "Cairo": 1, "Alexandria": 1 }
}
*/

// 3. Merge parsed real-world analysis into your original arrays
$users = [
    ['name' => 'Ahmed', 'national_id' => '29001011234567']
];
$analyzedUsers = $engine->mapWithAnalysis($users, 'national_id');
// Output will now contain an 'analysis' array attached to Ahmed's row with his birthday, gender, etc.
```

## Usage in Laravel

The package automatically registers a ServiceProvider for Laravel. It provides a new validation rule called `national_id`, which you can use directly in your Form Requests or inline validation.

```php
use Illuminate\Http\Request;

public function store(Request $request)
{
    $request->validate([
        'national_id_field' => ['required', 'string', 'national_id'],
    ]);

    // Or the string syntax
    // 'national_id_field' => 'required|string|national_id',

    // ...
}
```

Or you can use the built-in Rule object:

```php
use Aroon\EgyptianNationalId\Rules\NationalIdRule;

public function store(Request $request)
{
    $request->validate([
        'national_id_field' => ['required', new NationalIdRule()],
    ]);
}
```

## Testing

Run the included PHPUnit tests with:

```bash
composer test
# or
./vendor/bin/phpunit
```

## License

MIT License.

## Note

This package is based on the algorithm and logic from [mahmoudEbeid2/egyptian-national-id](https://github.com/mahmoudEbeid2/egyptian-national-id).
