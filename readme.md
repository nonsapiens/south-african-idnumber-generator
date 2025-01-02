# RSA (South African) ID Number Generator

This PHP/Laravel Faker library generates valid South African ID numbers, adhering to the 13-digit format specified by the Department of Home Affairs.
It is also able to validate RSA ID numbers, and provide information about the ID number holder.

The generated ID numbers include the date of birth, gender, citizenship status, and a checksum digit for validation.

## Features
- Generates valid South African ID numbers.
- Supports male and female gender identification.
- Includes citizenship status (South African citizen or permanent resident).

## Requirements
- PHP 7.4 or higher
- Laravel 9 or higher

## Installation

### Via Composer
```bash
composer require nonsapiens/south-african-id-number-faker
```

## Usage (Generation)

### With Faker
```php
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name,
            'email' => $this->faker->safeEmailAddress(),
            'rsa_id_number' => $this->faker->southAfricanIdNumber(),  # Completely random
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt('12345'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'last_accessed_at' => Carbon::now(),
            'is_active' => $this->faker->boolean(),
            'settings_json' => $this->faker->words(),
        ];
    }
}
```

You can also generate using:

**Specified details**

```php 
$this->faker->southAfricanIdNumber('1982-08-01', 'm');
```

**Gender specific**

```php
$this->faker->southAfricanIdNumberFemale()
```
```php
$this->faker->southAfricanIdNumberMale(Carbon::parse('1985-01-09'))
````

### Using the helper class
```php
use Illuminate\Support\Carbon;
use Nonsapiens\SouthAfricanIdNumberFaker;

// Example: Generate an ID for a male South African citizen born on 15 August 1995, using Carbon
$dateOfBirth = Carbon::createFromDate(1995, 8, 15);
$idNumber1 = RsaIdNumber::generateRsaIdNumber($dateOfBirth, 'm', true);

// Example: Generate for a female, random date of birth, non-citizen
$idNumber2 = RsaIdNumber::generateRsaIdNumber(null, 'f', false);
```

## Usage (validation)

### Using the helper class
```php
$idNumber = new RsaIdNumber('8208015009088');     # This will also accept ID numbers with spaces
if ($idNumber->isValid()) {
    echo "This ID number is {$idNumber->gender()}";
} else {
    echo 'Invalid ID number';
}
```

Other validation features include:

```php
$idNumber->dateOfBirth();
$idNumber->age();
$idNumber->isAdult();
$idNumber->isCitizen();
$idNumber->isPermanentResident();
```

You can also have the ID number formatted for easier reading:

```php
$idNumber = new RsaIdNumber('7504220045086');
echo $idNumber->toNatural();     // Echoes as "750422 0045 08 6"
```

## License
This library is open-source and available under the [MIT License](LICENSE).

## Contributions
Contributions, issues, and feature requests are welcome! Feel free to open a pull request or submit an issue on the repository.

## Disclaimer
This library is for educational and testing purposes only. Do not use the generated ID numbers for illegal or unethical purposes.

## About the author
[Stuart Steedman](https://www.linkedin.com/in/stuart-steedman/) is the CTO of [Sebenza](https://sebenza.tech), a [DNI company](https://www.dninvest.co.za/), operating out of Bryanston, South Africa.
Stuart enjoys [public speaking](https://www.youtube.com/watch?v=S5bjGo7EF5c), and enjoys prototyping complex software in Laravel.