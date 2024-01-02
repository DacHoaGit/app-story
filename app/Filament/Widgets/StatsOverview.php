<?php

namespace App\Filament\Widgets;

use App\Models\Story;
use App\Models\StoryType;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $users = User::where('role', 'client')->count();
        $types = StoryType::count();
        $stories = Story::count();
        return [
            Stat::make('Total users', $users),
            Stat::make('Total story Types', $types),
            Stat::make('Total stories', $stories),
        ];
    }
}
