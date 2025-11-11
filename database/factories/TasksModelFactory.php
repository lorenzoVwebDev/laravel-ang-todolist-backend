<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TasksModel>
 */
class TasksModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
         '_user_id' => 'f6f757645b76111d4162479047',
         'label' => 'default-task',
         'description' => 'lorem ipsum',
         'dueDate' => 1761654416,
         'addDate' => 1761305216,
         'done' => false,
         'type' => 'school'
        ];
    }
}
