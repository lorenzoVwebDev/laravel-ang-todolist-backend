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
         '_user_id' => '467cf1116e9c5be7f1f8cd933c',
         'label' => 'default-task',
         'description' => 'lorem ipsum',
         'dueDate' => 1761654416,
         'addDate' => 1761305216,
         'done' => false,
         'type' => 'school'
        ];
    }
}
