<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Settings\HomepageSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Pages\SettingsPage;

class ManageHomepage extends Page
{
    use InteractsWithForms;  // tambahkan trait

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Homepage Settings';
    
    public $hero_title = '';
    
    public function mount(): void
    {
        $settings = app(HomepageSettings::class);
        
        $this->form->fill([
            'hero_title' => $settings->hero_title,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hero Section')
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Title')
                            ->required()
                            ->maxLength(150),
                    ])
            ]);
    }
    
    public function submit(): void
    {
        $settings = app(HomepageSettings::class);
        
        $settings->hero_title = $this->form->getState()['hero_title'];        
        $settings->save();
        
        Notification::make()
            ->success()
            ->title('Homepage settings updated successfully')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('delete')
                ->label('Delete Settings')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Delete Settings')
                ->modalDescription('Are you sure you want to delete these settings? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete')
                ->action(function() {
                    $settings = app(HomepageSettings::class);
                    
                    // Reset nilai ke default/kosong
                    $settings->hero_title = '';
                    $settings->save();

                    Notification::make()
                        ->success()
                        ->title('Settings deleted successfully')
                        ->send();
                })
        ];
    }

    protected static string $view = 'filament.pages.manage-homepage';
}
