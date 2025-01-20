<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $indexExists = function ($table, $index) {
            $connection = Schema::getConnection();
            $database = $connection->getDatabaseName();

            $query = "SELECT COUNT(1) 
                    FROM INFORMATION_SCHEMA.STATISTICS 
                    WHERE table_schema = ? AND table_name = ? AND index_name = ?";

            return (bool) $connection->selectOne($query, [$database, $table, $index])->{'COUNT(1)'};
        };

        // Add indexes to the applicants table
        Schema::table('applicants', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('applicants', 'idx_applicants_gender_id')) {
                $table->index('gender_id', 'idx_applicants_gender_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_race_id')) {
                $table->index('race_id', 'idx_applicants_race_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_education_id')) {
                $table->index('education_id', 'idx_applicants_education_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_duration_id')) {
                $table->index('duration_id', 'idx_applicants_duration_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_employment')) {
                $table->index('employment', 'idx_applicants_employment');
            }
            if (!$indexExists('applicants', 'idx_applicants_age')) {
                $table->index('age', 'idx_applicants_age');
            }
            if (!$indexExists('applicants', 'idx_applicants_literacy_score')) {
                $table->index('literacy_score', 'idx_applicants_literacy_score');
            }
            if (!$indexExists('applicants', 'idx_applicants_numeracy_score')) {
                $table->index('numeracy_score', 'idx_applicants_numeracy_score');
            }
            if (!$indexExists('applicants', 'idx_applicants_situational_score')) {
                $table->index('situational_score', 'idx_applicants_situational_score');
            }
            if (!$indexExists('applicants', 'idx_applicants_score')) {
                $table->index('score', 'idx_applicants_score');
            }
            if (!$indexExists('applicants', 'idx_applicants_created_at')) {
                $table->index('created_at', 'idx_applicants_created_at');
            }
            if (!$indexExists('applicants', 'idx_applicants_state_id')) {
                $table->index('state_id', 'idx_applicants_state_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_shortlist_id')) {
                $table->index('shortlist_id', 'idx_applicants_shortlist_id');
            }
            if (!$indexExists('applicants', 'idx_applicants_appointed_id')) {
                $table->index('appointed_id', 'idx_applicants_appointed_id');
            }
        });

        // Add indexes to the shortlists table
        Schema::table('shortlists', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('shortlists', 'idx_shortlists_vacancy_id')) {
                $table->index('vacancy_id', 'idx_shortlists_vacancy_id');
            }
        });

        // Add indexes to the interviews table
        Schema::table('interviews', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('interviews', 'idx_interviews_vacancy_id')) {
                $table->index('vacancy_id', 'idx_interviews_vacancy_id');
            }
        });

        // Add indexes to the vacancies table
        Schema::table('vacancies', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('vacancies', 'idx_vacancies_store_id')) {
                $table->index('store_id', 'idx_vacancies_store_id');
            }
        });

        // Add indexes to the stores table
        Schema::table('stores', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('stores', 'idx_stores_division_id')) {
                $table->index('division_id', 'idx_stores_division_id');
            }
            if (!$indexExists('stores', 'idx_stores_region_id')) {
                $table->index('region_id', 'idx_stores_region_id');
            }
        });

        // Add indexes to the vacancy_fills table
        Schema::table('vacancy_fills', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('vacancy_fills', 'idx_vacancy_fills_created_at')) {
                $table->index('created_at', 'idx_vacancy_fills_created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove indexes from the applicants table
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('idx_applicants_gender_id');
            $table->dropIndex('idx_applicants_race_id');
            $table->dropIndex('idx_applicants_education_id');
            $table->dropIndex('idx_applicants_duration_id');
            $table->dropIndex('idx_applicants_employment');
            $table->dropIndex('idx_applicants_age');
            $table->dropIndex('idx_applicants_literacy_score');
            $table->dropIndex('idx_applicants_numeracy_score');
            $table->dropIndex('idx_applicants_situational_score');
            $table->dropIndex('idx_applicants_score');
            $table->dropIndex('idx_applicants_created_at');
            $table->dropIndex('idx_applicants_state_id');
            $table->dropIndex('idx_applicants_shortlist_id');
            $table->dropIndex('idx_applicants_appointed_id');
        });

        // Remove indexes from the shortlists table
        Schema::table('shortlists', function (Blueprint $table) {
            $table->dropIndex('idx_shortlists_vacancy_id');
        });

        // Remove indexes from the interviews table
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropIndex('idx_interviews_vacancy_id');
        });

        // Remove indexes from the vacancies table
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropIndex('idx_vacancies_store_id');
        });

        // Remove indexes from the stores table
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('idx_stores_division_id');
            $table->dropIndex('idx_stores_region_id');
        });

        // Remove indexes from the vacancy_fills table
        Schema::table('vacancy_fills', function (Blueprint $table) {
            $table->dropIndex('idx_vacancy_fills_created_at');
        });
    }
};
