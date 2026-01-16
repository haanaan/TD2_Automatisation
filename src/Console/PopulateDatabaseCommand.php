<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory as FakerFactory;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Populate database with random data...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $faker = FakerFactory::create('fr_FR');

        $companiesCount = rand(2, 4);
        $totalEmployees = 10;
        $employeesCreated = 0;

        for ($i = 0; $i < $companiesCount; $i++) {

            // Création société
            $company = new Company();
            $company->name = $faker->company;
            $company->phone = $faker->phoneNumber;
            $company->email = $faker->companyEmail;
            $company->website = $faker->url;
            $company->image = $faker->imageUrl(800, 600, 'business');
            $company->save();


            $officesCount = rand(2, 3);
            $offices = [];

            // Création bureaux
            for ($j = 0; $j < $officesCount; $j++) {
                $office = new Office();
                $office->name = 'Bureau ' . $faker->city;
                $office->address = $faker->streetAddress;
                $office->city = $faker->city;
                $office->zip_code = $faker->postcode;
                $office->country = $faker->country;
                $office->email = $faker->optional()->companyEmail;
                $office->phone = $faker->optional()->phoneNumber;
                $office->company_id = $company->id;
                $office->save();

                $offices[] = $office;
            }

            // Définir le siège social
            $company->head_office_id = $offices[0]->id;
            $company->save();

            // Création employés
            foreach ($offices as $office) {
                if ($employeesCreated >= $totalEmployees) {
                    break 2;
                }

                $employeesPerOffice = rand(1, 3);

                for ($k = 0; $k < $employeesPerOffice; $k++) {
                    if ($employeesCreated >= $totalEmployees) {
                        break;
                    }

                    $employee = new Employee();
                    $employee->first_name = $faker->firstName;
                    $employee->last_name = $faker->lastName;
                    $employee->office_id = $office->id;
                    $employee->email = $faker->unique()->safeEmail;
                    $employee->phone = $faker->optional()->phoneNumber;
                    $employee->job_title = $faker->jobTitle;
                    $employee->save();

                    $employeesCreated++;
                }
            }
        }

        $output->writeln('Database populated successfully!');
        return Command::SUCCESS;
    }

}