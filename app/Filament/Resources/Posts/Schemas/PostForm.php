<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from the title.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('posts'),
                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(500),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->helperText('Leave blank to keep as a draft.'),
            ]);
    }
}
