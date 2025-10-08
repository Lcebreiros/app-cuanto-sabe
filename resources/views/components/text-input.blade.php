@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'rounded-md shadow-sm border border-[#004466] bg-[#1a1333] text-[#00f0ff] placeholder-[#66cce6] focus:border-[#00f0ff] focus:ring focus:ring-[#00f0ff] focus:ring-opacity-50',
        'style' => 'background-color: #1a1333; color: #00f0ff;'
    ]) }}
    placeholder="{{ $attributes->get('placeholder') ?? '' }}"
/>

<style>
    input::placeholder {
        color: #66cce6 !important;
        opacity: 1 !important;
    }
</style>
