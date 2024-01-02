<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use App\Models\Chapter;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        
        $data['number'] = Chapter::where('story_id', $data['story_id'])->count() + 1;

        return $data;
    }
}
