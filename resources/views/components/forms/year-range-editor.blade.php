@props(['startYear', 'endYear'])

<div class="bg-blue-100 dark:bg-blue-900/40 p-4 rounded-lg shadow-md border-l-4 border-blue-500 relative">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Rango de Análisis</p>
            <div class="flex items-center space-x-2" id="year-range-display">
                <span id="start-year-display" 
                    class="text-2xl font-bold text-gray-900 dark:text-gray-100 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800/60 px-2 py-1 rounded transition-colors duration-200"
                    data-original="{{ $startYear }}" 
                    ondblclick="enableYearEdit('start')">
                    {{ $startYear }} 
                </span>
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">-</span>
                <span id="end-year-display" 
                    class="text-2xl font-bold text-gray-900 dark:text-gray-100 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800/60 px-2 py-1 rounded transition-colors duration-200"
                    data-original="{{ $endYear }}" 
                    ondblclick="enableYearEdit('end')">
                    {{ $endYear }} 
                </span>
            </div>
            <div id="year-range-edit" class="hidden mt-2">
                <div class="flex items-center space-x-2">
                    <input type="number" 
                        id="start-year-input" 
                        class="w-24 p-2 text-center border border-blue-300 dark:border-blue-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        min="2000" 
                        max="{{ date('Y') }}" 
                        value="{{ $startYear }}"> 
                    <span class="text-lg font-bold text-gray-700 dark:text-gray-300">-</span>
                    <input type="number" 
                        id="end-year-input" 
                        class="w-24 p-2 text-center border border-blue-300 dark:border-blue-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        min="2000" 
                        max="{{ date('Y') }}" 
                        value="{{ $endYear }}"> 
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    Enter para guardar o Esc para cancelar
                </p>
            </div>
        </div>
        
        <div id="reanalyze-button-container" class="hidden">
            <button id="reanalyze-button" 
                    onclick="reanalyzeWithNewRange()"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium flex items-center space-x-2 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 2v6h-6"/>
                    <path d="M3 12a9 9 0 0 1 15-6.7L21 8"/>
                    <path d="M3 22v-6h6"/>
                    <path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>
                </svg>
                <span id="reanalyze-button-text">Reanalizar con nuevo rango</span>
                <span id="reanalyze-button-spinner" class="hidden ml-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>