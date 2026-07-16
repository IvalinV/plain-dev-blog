<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;
use Livewire\Component;

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
                TextInput::make('url')
                    ->label('URL')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?Post $record): ?string => $record?->url)
                    ->helperText('Auto-generated from the slug. Available after the post is saved.')
                    ->suffixAction(
                        Action::make('copyUrl')
                            ->icon(Heroicon::Clipboard)
                            ->tooltip('Copy to clipboard')
                            ->visible(fn (?string $state): bool => filled($state))
                            ->action(function (?string $state, Component $livewire): void {
                                $livewire->js('window.navigator.clipboard.writeText('.Js::from($state).')');

                                Notification::make()
                                    ->title('URL copied to clipboard')
                                    ->success()
                                    ->send();
                            }),
                    ),
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
                    ->disk('s3')
                    ->directory('posts'),
                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(500),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->native(false)
                    ->helperText('Leave blank to keep as a draft. Stored as-is in UTC; timezone conversion is handled on the frontend.'),
            ]);
    }
}
