<?php

namespace App\Filament\Resources\Authors\Tables;

use App\Models\Author;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->label('Open')
                    ->url(fn (Author $record) => "/authors/$record->slug")
                    ->openUrlInNewTab(),
                ViewAction::make()->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
