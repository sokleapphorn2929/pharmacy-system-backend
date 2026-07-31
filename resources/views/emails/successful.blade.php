<x-mail::message>
# Order Successful! 🚀

Thank you for your purchase. Your payment has been successfully processed.

**Order ID:** {{ $orderData['_id'] ?? $orderData['id'] }}  
**Total Amount:** ${{ $orderData['total_price'] ?? 'N/A' }}

You can log in to your dashboard anytime to check your ongoing order status and notifications.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>