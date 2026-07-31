<x-mail::message>
@slot('header')
    <a href="https://pharmacy-frontend-taupe.vercel.app/">
        KH Pharmacy
    </a>
@endslot

# Order Successful! 🚀

Thank you for your purchase. Your payment has been successfully processed.

**Order ID:** {{ $orderData['_id'] ?? $orderData['id'] }}  
**Total Amount:** ${{ $orderData['total_price'] ?? 'N/A' }}

You can log in to your dashboard anytime to check your ongoing order status and notifications.

Thanks,<br>
KH Pharmacy by Sokleap
</x-mail::message>