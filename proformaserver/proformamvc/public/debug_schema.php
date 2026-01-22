try {
    require_once __DIR__ . '/../app/Helpers/SettingsHelper.php';
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/Model.php';
    require_once __DIR__ . '/../app/Models/Setting.php';
    
    // Mocking config just in case
    
    $settings = \App\Models\Setting::getAllStatic(); // Or instantiate
    $model = new \App\Models\Setting();
    $all = $model->getAll();
    print_r(array_keys($all));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
