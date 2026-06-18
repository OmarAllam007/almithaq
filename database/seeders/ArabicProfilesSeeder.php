<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArabicProfilesSeeder extends Seeder
{
    private array $maleFirstNames = [
        'محمد', 'أحمد', 'عبدالله', 'عمر', 'علي', 'خالد', 'يوسف', 'فيصل',
        'عبدالرحمن', 'إبراهيم', 'سلطان', 'نواف', 'تركي', 'وليد', 'راشد',
        'ناصر', 'طارق', 'كريم', 'زياد', 'معاذ', 'أنس', 'بلال', 'مصطفى',
        'محمود', 'ماجد', 'هاشم', 'سعيد', 'أيمن', 'رامي', 'شريف', 'حازم',
        'منصور', 'سعد', 'بدر', 'حمد', 'غانم', 'جاسم', 'قاسم', 'عصام',
        'هيثم', 'عادل', 'باسم', 'سامر', 'ياسر', 'نبيل', 'فادي', 'حسام',
    ];

    private array $femaleFirstNames = [
        'فاطمة', 'عائشة', 'مريم', 'زينب', 'رقية', 'سارة', 'هند', 'ريم',
        'نور', 'أسماء', 'خديجة', 'لمياء', 'غادة', 'ليلى', 'دينا', 'شيماء',
        'منى', 'وفاء', 'هدى', 'أميرة', 'ملك', 'لجين', 'تهاني', 'صفاء',
        'رانيا', 'إيمان', 'سلمى', 'جواهر', 'لطيفة', 'روان', 'حنان', 'نادية',
        'سلوى', 'عبير', 'آمنة', 'رشا', 'مها', 'أريج', 'بسمة', 'ولاء',
        'نجوى', 'سمر', 'رند', 'إسراء', 'أفنان', 'شهد', 'ديمة', 'ريهام',
    ];

    private array $lastNames = [
        'الأحمد', 'المحمود', 'العلي', 'الزهراني', 'القحطاني', 'الشهري',
        'الغامدي', 'العتيبي', 'الدوسري', 'الحربي', 'الشمري', 'العنزي',
        'السيد', 'الشرقاوي', 'الجوهري', 'البكري', 'الزيات', 'البوزيدي',
        'الإدريسي', 'الحسني', 'الفاسي', 'العمري', 'الحضرمي', 'الصنعاني',
        'الزبيدي', 'الهمداني', 'الشامي', 'الدمشقي', 'الحلبي', 'الزعبي',
        'العباس', 'الأنصاري', 'المسعودي', 'الفارس', 'الصالح', 'الراشد',
        'الخالد', 'الناصر', 'الخليل', 'القاسم', 'الحسين', 'الرشيد',
        'البلوي', 'المطيري', 'السلمي', 'الرفاعي', 'الحمادي', 'الجابر',
    ];

    /** @var array<int, array{id: int, phone_code: string}> */
    private array $countryPool = [];

    public function run(): void
    {
        $this->buildCountryPool();
        $this->createUsers(200, 1);
        $this->createUsers(300, 2);
    }

    private function createUsers(int $count, int $registrationType): void
    {
        $firstNames = $registrationType === 1 ? $this->maleFirstNames : $this->femaleFirstNames;

        for ($i = 0; $i < $count; $i++) {
            $firstName = fake()->randomElement($firstNames);
            $lastName = fake()->randomElement($this->lastNames);
            $fullName = "$firstName $lastName";
            $country = fake()->randomElement($this->countryPool);

            User::factory()->create([
                'name' => $fullName,
                'full_name' => $fullName,
                'username' => 'u_'.Str::random(10),
                'registration_type' => $registrationType,
                'marriage_type' => $registrationType === 1 ? rand(1, 2) : rand(3, 4),
                'marriage_status' => $registrationType === 1 ? rand(1, 4) : 1,
                'beard' => $registrationType === 1 ? rand(0, 1) : 0,
                'nationality' => $country['id'],
                'residence' => $country['id'],
                'city' => null,
                'country_code' => $country['phone_code'],
                'phone_number' => fake()->unique()->numerify('5########'),
                'age' => $registrationType === 1 ? rand(25, 60) : rand(18, 45),
                'profile_completed' => true,
            ]);
        }
    }

    private function buildCountryPool(): void
    {
        $weighted = [
            ['id' => 151, 'phone_code' => '+966', 'weight' => 30], // Saudi Arabia
            ['id' => 52,  'phone_code' => '+20',  'weight' => 20], // Egypt
            ['id' => 119, 'phone_code' => '+212', 'weight' => 16], // Morocco
            ['id' => 193, 'phone_code' => '+967', 'weight' => 8],  // Yemen
            ['id' => 170, 'phone_code' => '+963', 'weight' => 6],  // Syria
            ['id' => 3,   'phone_code' => '+213', 'weight' => 5],  // Algeria
            ['id' => 85,  'phone_code' => '+962', 'weight' => 4],  // Jordan
            ['id' => 92,  'phone_code' => '+965', 'weight' => 3],  // Kuwait
            ['id' => 184, 'phone_code' => '+971', 'weight' => 3],  // UAE
            ['id' => 141, 'phone_code' => '+974', 'weight' => 2],  // Qatar
            ['id' => 79,  'phone_code' => '+964', 'weight' => 2],  // Iraq
            ['id' => 178, 'phone_code' => '+216', 'weight' => 1],  // Tunisia
        ];

        foreach ($weighted as $country) {
            $weight = $country['weight'];
            unset($country['weight']);

            for ($i = 0; $i < $weight; $i++) {
                $this->countryPool[] = $country;
            }
        }
    }
}
