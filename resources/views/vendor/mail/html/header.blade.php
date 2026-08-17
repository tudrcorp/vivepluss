@props(['url'])
@php
    $branding = \App\Support\Mail\MailBranding::forWhiteCompany(null);
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($branding['logoPath'])
<img src="{{ $message->embed($branding['logoPath']) }}" class="logo" alt="VivePlus">
@else
VivePlus
@endif
</a>
</td>
</tr>
