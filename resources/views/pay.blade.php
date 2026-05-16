<!DOCTYPE html>
<html>

<head>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="ISI_CLIENT_KEY_KAMU"></script>
</head>

<body>

    <button onclick="payNow()">Pay</button>

    <script>
        function payNow() {
            snap.pay("{{ $token }}");
        }
    </script>

</body>

</html>
