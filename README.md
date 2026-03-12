# Egyptian National ID

A PHP and Laravel package to parse and validate Egyptian National IDs (14 digits).

Based on the algorithm and logic from [mahmoudEbeid2/egyptian-national-id](https://github.com/mahmoudEbeid2/egyptian-national-id).

## Installation

You can install the package via Composer:

```bash
composer require aroon/egyptian-national-id-checker
```

*(Note: Ensure you are requiring the local path in your project if you haven't published it to Packagist).*

## Features
- **Sanitization**: Automatically cleans input by converting Arabic/Hindi numerals (٠-٩) to English numerals and stripping spaces or dashes.
- **Validation**: Strict validation of the 14-digit format, century, birth date, governorate code, and check digit.
- **Data extraction**: Easily extract birth date, gender, governorate, and age.

## Usage in PHP

```php
use Aroon\EgyptianNationalId\EgyptianNationalId;

// It automatically cleans spaces, dashes, and Arabic/Hindi numbers!
$id = new EgyptianNationalId('٢٩٠-٠١٠١ ١٢٣ ٤٥٦٧');

// Validate the ID (length, date, century, governorate, and check digit)
$isValid = $id->isValid(); // boolean

if ($isValid) {
    // Parsing information
    $id->getBirthYear();      // 1990
    $id->getBirthMonth();     // 1
    $id->getBirthDay();       // 1
    $id->getAge();            // e.g. 34 (based on current year)
    
    $id->getGovernorateCode(); // "12"
    $id->getGovernorateName(); // "Dakahlia"
    $id->getRegion();          // "Delta"
    
    $id->getGender();          // "female"
    $id->isMale();             // false
    $id->isFemale();           // true
    
    $id->isAdult();            // true
    $id->isInsideEgypt();      // true
    
    // Get all parsed data as an array
    $data = $id->toArray();
}
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
