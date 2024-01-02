<?php

namespace App\Filament\Resources\StoryTypeResource\Pages;

use App\Filament\Resources\StoryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoryType extends EditRecord
{
    protected static string $resource = StoryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
