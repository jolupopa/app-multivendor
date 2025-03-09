<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatusEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use app\Enums\RolesEnum;
use App\Models\Departament;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        TextInput::make('title')
                            ->live(onBlur: true)
                            ->required()
                            ->afterStateUpdated(function (string $operation, $state, callable $set ) {
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->required(),  
                        Select::make('departament_id')
                            ->relationship('departament', 'name')
                            ->label(__('Departament'))  
                            ->preload()
                            ->searchable()
                            ->required()
                            ->reactive() //makes the field reactive to changes
                            ->afterStateUpdated(function ( callable $set ) {
                                $set('category_id', null); //reset category when departament change
                            }),
                        Select::make('category_id') 
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: function(Builder $query, callable $get) {
                                    // Modify the category query base on the selected departament
                                    $departamentId = $get('departament_id'); // get selected departament 
                                    if ($departamentId){
                                        $query->where('departament_id', $departamentId); // Filter categories based on departament
                                    }
                                }
                            )
                            ->label(__('Category'))
                            ->preload()
                            ->searchable()
                            ->required()

                    ]),

                Forms\Components\RichEditor::make('description')  
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                        'table'
                    ])
                    ->columnSpan(2),
                TextInput::make('price')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->integer(),
                Select::make('status')  
                    ->options(ProductStatusEnum::labels()) 
                    ->default(ProductStatusEnum::Draft->value) 
                    ->required(),  

                        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->words(10)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors(ProductStatusEnum::colors()),
                Tables\Columns\TextColumn::make('departament.name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductStatusEnum::labels()),
                SelectFilter::make('departament_id')
                    ->relationship('departament', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
         /** @var User $user */
         $user = Auth::user();
       
       //  $user = auth()->user();

        return $user && $user->hasRole(RolesEnum::Vendor);

    }
}
