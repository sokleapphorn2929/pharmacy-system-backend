@props(['url'])
<tr>
<td class="header">
<a href="https://pharmacy-frontend-taupe.vercel.app/" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://res.cloudinary.com/ds1e6ptad/image/upload/v1785514196/pharmacy_logo_fpb0hs.png" class="logo" alt="Kh Pharmacy Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
