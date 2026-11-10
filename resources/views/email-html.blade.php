<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div class="w-full overflow-hidden">
        {{-- sandbox: logged email HTML must never run scripts in the panel's origin; links may still open in a new tab --}}
        <iframe srcdoc="{{ $getState() }}" sandbox="allow-popups allow-popups-to-escape-sandbox" seamless style="width:100%; height:24rem; display:block;" frameborder="0"></iframe>
    </div>
</x-dynamic-component>
