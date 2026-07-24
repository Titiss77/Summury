<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\Constraints\SeeInDatabase;
use Config\Database;
use Config\Migrations;
use Config\Services;
use PHPUnit\Framework\Attributes\AfterClass;

/**
 * DatabaseTestTrait.
 *
 * Provides functionality for refreshing/seeding
 * the database during testing.
 *
 * @mixin CIUnitTestCase
 */
trait DatabaseTestTrait
{
    /**
     * Is db migration done once or more than once?
     *
     * @var bool
     */
    private static $doneMigration = false;

    /**
     * Is seeding done once or more than once?
     *
     * @var bool
     */
    private static $doneSeed = false;

    /**
     * Load any database test dependencies.
     */
    public function loadDependencies(): void
    {
        if (null === $this->db) {
            $this->db = Database::connect($this->DBGroup);
            $this->db->initialize();
        }

        if (null === $this->migrations) {
            // Ensure that we can run migrations
            $config = new Migrations();
            $config->enabled = true;

            $this->migrations = Services::migrations($config, $this->db, false);
            $this->migrations->setSilent(false);
        }

        if (null === $this->seeder) {
            $this->seeder = Database::seeder($this->DBGroup);
            $this->seeder->setSilent(true);
        }
    }

    /**
     * Seeds that database with a specific seeder.
     */
    public function seed(string $name): void
    {
        $this->seeder->call($name);
    }

    // --------------------------------------------------------------------
    // Utility
    // --------------------------------------------------------------------
    /**
     * Reset $doneMigration and $doneSeed.
     */
    #[AfterClass]
    public static function resetMigrationSeedCount(): void
    {
        self::$doneMigration = false;
        self::$doneSeed = false;
    }

    /**
     * Loads the Builder class appropriate for the current database.
     *
     * @return BaseBuilder
     */
    public function loadBuilder(string $tableName)
    {
        $builderClass = str_replace('Connection', 'Builder', $this->db::class);

        return new $builderClass($tableName, $this->db);
    }

    /**
     * Fetches a single column from a database row with criteria
     * matching $where.
     *
     * @param array<string, mixed> $where
     *
     * @return bool
     *
     * @throws DatabaseException
     */
    public function grabFromDatabase(string $table, string $column, array $where)
    {
        $query = $this->db->table($table)
            ->select($column)
            ->where($where)
            ->get()
        ;

        $query = $query->getRow();

        return $query->{$column} ?? false;
    }

    // --------------------------------------------------------------------
    // Assertions
    // --------------------------------------------------------------------

    /**
     * Asserts that records that match the conditions in $where DO
     * exist in the database.
     *
     * @param array<string, mixed> $where
     *
     * @throws DatabaseException
     */
    public function seeInDatabase(string $table, array $where): void
    {
        $constraint = new SeeInDatabase($this->db, $where);
        static::assertThat($table, $constraint);
    }

    /**
     * Asserts that records that match the conditions in $where do
     * not exist in the database.
     *
     * @param array<string, mixed> $where
     */
    public function dontSeeInDatabase(string $table, array $where): void
    {
        $count = $this->db->table($table)
            ->where($where)
            ->countAllResults()
        ;

        $this->assertTrue(0 === $count, 'Row was found in database');
    }

    /**
     * Inserts a row into to the database. This row will be removed
     * after the test has run.
     *
     * @param array<string, mixed> $data
     *
     * @return bool
     */
    public function hasInDatabase(string $table, array $data)
    {
        $this->insertCache[] = [
            $table,
            $data,
        ];

        return $this->db->table($table)->insert($data);
    }

    /**
     * Asserts that the number of rows in the database that match $where
     * is equal to $expected.
     *
     * @param array<string, mixed> $where
     *
     * @throws DatabaseException
     */
    public function seeNumRecords(int $expected, string $table, array $where): void
    {
        $count = $this->db->table($table)
            ->where($where)
            ->countAllResults()
        ;

        $this->assertEquals($expected, $count, 'Wrong number of matching rows in database.');
    }

    // --------------------------------------------------------------------
    // Staging
    // --------------------------------------------------------------------

    /**
     * Runs the trait set up methods.
     */
    protected function setUpDatabase(): void
    {
        $this->loadDependencies();
        $this->setUpMigrate();
        $this->setUpSeed();
    }

    /**
     * Runs the trait set up methods.
     */
    protected function tearDownDatabase(): void
    {
        $this->clearInsertCache();
    }

    // --------------------------------------------------------------------
    // Migrations
    // --------------------------------------------------------------------

    /**
     * Migrate on setUp.
     */
    protected function setUpMigrate(): void
    {
        if (false === $this->migrateOnce || false === self::$doneMigration) {
            if (true === $this->refresh) {
                $this->regressDatabase();

                // Reset counts on faked items
                Fabricator::resetCounts();
            }

            $this->migrateDatabase();
        }
    }

    /**
     * Regress migrations as defined by the class.
     */
    protected function regressDatabase(): void
    {
        if (false === $this->migrate) {
            return;
        }

        // If no namespace was specified then rollback all
        if (null === $this->namespace) {
            $this->migrations->setNamespace(null);
            $this->migrations->regress(0, 'tests');
        }

        // Regress each specified namespace
        else {
            $namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

            foreach ($namespaces as $namespace) {
                $this->migrations->setNamespace($namespace);
                $this->migrations->regress(0, 'tests');
            }
        }
    }

    /**
     * Run migrations as defined by the class.
     */
    protected function migrateDatabase(): void
    {
        if (false === $this->migrate) {
            return;
        }

        // If no namespace was specified then migrate all
        if (null === $this->namespace) {
            $this->migrations->setNamespace(null);
            $this->migrations->latest('tests');
            self::$doneMigration = true;
        }
        // Run migrations for each specified namespace
        else {
            $namespaces = is_array($this->namespace) ? $this->namespace : [$this->namespace];

            foreach ($namespaces as $namespace) {
                $this->migrations->setNamespace($namespace);
                $this->migrations->latest('tests');
                self::$doneMigration = true;
            }
        }
    }

    // --------------------------------------------------------------------
    // Seeds
    // --------------------------------------------------------------------

    /**
     * Seed on setUp.
     */
    protected function setUpSeed(): void
    {
        if (false === $this->seedOnce || false === self::$doneSeed) {
            $this->runSeeds();
        }
    }

    /**
     * Run seeds as defined by the class.
     */
    protected function runSeeds(): void
    {
        if ('' !== $this->seed) {
            if ('' !== $this->basePath) {
                $this->seeder->setPath(rtrim($this->basePath, '/').'/Seeds');
            }

            $seeds = is_array($this->seed) ? $this->seed : [$this->seed];

            foreach ($seeds as $seed) {
                $this->seed($seed);
            }
        }

        self::$doneSeed = true;
    }

    /**
     * Removes any rows inserted via $this->hasInDatabase().
     */
    protected function clearInsertCache(): void
    {
        foreach ($this->insertCache as $row) {
            $this->db->table($row[0])
                ->where($row[1])
                ->delete()
            ;
        }
    }

    /**
     * Sets $DBDebug to false.
     *
     * WARNING: this value will persist! take care to roll it back.
     */
    protected function disableDBDebug(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
    }

    /**
     * Sets $DBDebug to true.
     */
    protected function enableDBDebug(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', true);
    }
}
