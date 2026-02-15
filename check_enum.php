use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = DB::select("SHOW COLUMNS FROM documentacion WHERE Field LIKE '%_status'");
print_r($columns);
