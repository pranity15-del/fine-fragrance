    </div>
    <!-- Main Content End -->

    <script>
    document.addEventListener('DOMContentLoaded', function(){
      var btn = document.getElementById('userMenu');
      if (btn) {
        try { bootstrap.Dropdown.getOrCreateInstance(btn); } catch (e) { }
      }
    });
    </script>
</body>
</html>