@if(Session::has('redirect_to'))
    <script>
        window.addEventListener('load', function() {
            window.location.href = '{{ Session::get("redirect_to") }}';
        });
    </script>
@endif
