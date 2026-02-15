use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('documentacion');
echo "Columns in 'documentacion':\n";
foreach ($columns as $col) {
    echo "- $col\n";
}
