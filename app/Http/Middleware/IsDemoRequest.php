<?php
    namespace App\Http\Middleware;

    use Closure;

    use Illuminate\Support\Facades\{DB, Config};

    class IsDemoRequest {

        /**
        * Handle an incoming request.
        *
        * @param  \Illuminate\Http\Request  $request
        * @param  \Closure  $next
        * @return mixed
        */
        public function handle($request, Closure $next) {

            if ($request->has("demo-db")) {

                DB::purge('mysql');

                Config::set('database.connections.mysql.database', 'demo_database');
            }
            return $next($request);
        }
    }
?>
