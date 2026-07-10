<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (Post $record): string => match (true) {
                        $record->published_at === null => 'Draft',
                        $record->published_at->isFuture() => 'Scheduled',
                        default => 'Published',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Scheduled' => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label('Publish state')
                    ->placeholder('All posts')
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts & scheduled')
                    ->queries(
                        true: fn (Builder $query): Builder => $query
                            ->whereNotNull('published_at')
                            ->where('published_at', '<=', now()),
                        false: fn (Builder $query): Builder => $query
                            ->where(fn (Builder $inner): Builder => $inner
                                ->whereNull('published_at')
                                ->orWhere('published_at', '>', now())),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
