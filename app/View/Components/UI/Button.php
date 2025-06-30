<?php

namespace App\View\Components\UI;

use Illuminate\View\Component;

class Button extends Component
{
    public function __construct(
        public string $variant = 'primary',
        public string $size = 'md',
        public ?string $type = 'button',
        public bool $disabled = false,
        public ?string $href = null
    ) {}

    public function getButtonClasses(): string
    {
        $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

        $variantClasses = match ($this->variant) {
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
            default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
        };

        $sizeClasses = match ($this->size) {
            'sm' => 'px-3 py-2 text-sm',
            'lg' => 'px-6 py-3 text-lg',
            default => 'px-4 py-2 text-base'
        };

        return trim($baseClasses.' '.$variantClasses.' '.$sizeClasses);
    }

    public function render()
    {
        return view('components.ui.button');
    }
}
