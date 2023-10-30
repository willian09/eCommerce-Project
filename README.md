    // To create Admin User //
- php artisan tinker
- $model = new User();
- $model->name = 'Admin'; (or another name...)
- $model->email = 'admin@example.it';
- $model->password = '12345678';
- $model->role = '1';
- $model->save();

    // To create Country List //
- php artisan db:seed --class=CountrySeeder
