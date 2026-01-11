<?php

namespace App\Filament\User\Resources\Documents;

use App\Enum\DocumentStatus;
use App\Filament\User\Resources\Documents\Pages\CreateDocument;
use App\Filament\User\Resources\Documents\Pages\EditDocument;
use App\Filament\User\Resources\Documents\Pages\ListDocuments;
use App\Filament\User\Resources\Documents\Pages\ViewDocument;
use App\Filament\User\Resources\Documents\Schemas\DocumentForm;
use App\Filament\User\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\User\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = 'solar-documents-linear';

    protected static ?string $recordTitleAttribute = 'document_number';
    protected static ?string $modelLabel = 'Documento';
    protected static ?string $pluralModelLabel = 'Documentos';
    protected static ?string $slug = 'documentos';
    //protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('area_origen_id', Auth::user()->office_id);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', DocumentStatus::IN_PROCESS)->count();
    }
    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }
}
