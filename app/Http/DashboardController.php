<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord approprié en fonction du rôle de l'utilisateur.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'hotel_manager':
                return $this->hotelManagerDashboard();
            case 'branch_manager':
                return $this->branchManagerDashboard();
            case 'customer':
                return $this->customerDashboard();
            default:
                return redirect()->route('login')->with('error', 'Rôle non reconnu.');
        }
    }
    
    /**
     * Tableau de bord pour l'administrateur du site.
     *
     * @return \Illuminate\Http\Response
     */
    private function adminDashboard()
    {
        // Statistiques globales pour l'administrateur
        $totalHotels = Hotel::count();
        $activeHotels = Hotel::where('status', 'active')->count();
        $hotelManagers = DB::table('users')->where('role', 'hotel_manager')->count();
        
        // Liste des hôtels avec leurs gestionnaires
        $hotels = Hotel::with('manager')->get();
        
        return view('dashboards.admin', compact('totalHotels', 'activeHotels', 'hotelManagers', 'hotels'));
    }
    
    /**
     * Tableau de bord pour le gestionnaire d'hôtel.
     *
     * @return \Illuminate\Http\Response
     */
    private function hotelManagerDashboard()
    {
        $user = Auth::user();
        
        // Récupérer l'hôtel géré par cet utilisateur
        $hotel = Hotel::where('manager_id', $user->id)->first();
        
        if (!$hotel) {
            return redirect()->route('login')->with('error', 'Aucun hôtel associé à votre compte.');
        }
        
        // Se connecter à la base de données du tenant pour récupérer les statistiques
        Config::set('database.connections.tenant.database', $hotel->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Statistiques pour le gestionnaire d'hôtel
        $totalBranches = DB::connection('tenant')->table('branches')->count();
        $totalRooms = DB::connection('tenant')->table('rooms')->count();
        $totalCustomers = DB::connection('tenant')->table('customers')->count();
        $totalBookings = DB::connection('tenant')->table('bookings')->count();
        
        // Chiffre d'affaires total
        $revenue = DB::connection('tenant')->table('bookings')
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('total_price');
        
        // Liste des filiales avec leurs gestionnaires
        $branches = DB::connection('tenant')->table('branches')
            ->join('users', 'branches.manager_id', '=', 'users.id')
            ->select('branches.*', 'users.name as manager_name', 'users.email as manager_email')
            ->get();
        
        return view('dashboards.hotel_manager', compact(
            'hotel', 
            'totalBranches', 
            'totalRooms', 
            'totalCustomers', 
            'totalBookings', 
            'revenue', 
            'branches'
        ));
    }
    
    /**
     * Tableau de bord pour le gestionnaire de filiale.
     *
     * @return \Illuminate\Http\Response
     */
    private function branchManagerDashboard()
    {
        $user = Auth::user();
        
        // Récupérer la filiale gérée par cet utilisateur
        $branch = DB::connection('tenant')->table('branches')
            ->where('manager_id', $user->id)
            ->first();
        
        if (!$branch) {
            return redirect()->route('login')->with('error', 'Aucune filiale associée à votre compte.');
        }
        
        // Statistiques pour le gestionnaire de filiale
        $totalRooms = DB::connection('tenant')->table('rooms')
            ->where('branch_id', $branch->id)
            ->count();
        
        $availableRooms = DB::connection('tenant')->table('rooms')
            ->where('branch_id', $branch->id)
            ->where('status', 'available')
            ->count();
        
        $totalCustomers = DB::connection('tenant')->table('customers')
            ->where('branch_id', $branch->id)
            ->count();
        
        $totalBookings = DB::connection('tenant')->table('bookings')
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->where('customers.branch_id', $branch->id)
            ->count();
        
        // Chiffre d'affaires de la filiale
        $revenue = DB::connection('tenant')->table('bookings')
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->where('customers.branch_id', $branch->id)
            ->where('bookings.status', 'completed')
            ->where('bookings.payment_status', 'paid')
            ->sum('bookings.total_price');
        
        // Liste des chambres
        $rooms = DB::connection('tenant')->table('rooms')
            ->where('branch_id', $branch->id)
            ->get();
        
        // Liste des clients
        $customers = DB::connection('tenant')->table('customers')
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->where('customers.branch_id', $branch->id)
            ->select('customers.*', 'users.name', 'users.email')
            ->get();
        
        // Réservations récentes
        $recentBookings = DB::connection('tenant')->table('bookings')
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->where('customers.branch_id', $branch->id)
            ->select('bookings.*', 'users.name as customer_name', 'rooms.room_number')
            ->orderBy('bookings.created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboards.branch_manager', compact(
            'branch', 
            'totalRooms', 
            'availableRooms', 
            'totalCustomers', 
            'totalBookings', 
            'revenue', 
            'rooms', 
            'customers', 
            'recentBookings'
        ));
    }
    
    /**
     * Tableau de bord pour le client.
     *
     * @return \Illuminate\Http\Response
     */
    private function customerDashboard()
    {
        $user = Auth::user();
        
        // Récupérer les informations du client
        $customer = DB::connection('tenant')->table('customers')
            ->where('user_id', $user->id)
            ->first();
        
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Aucun profil client associé à votre compte.');
        }
        
        // Récupérer la filiale associée au client
        $branch = DB::connection('tenant')->table('branches')
            ->where('id', $customer->branch_id)
            ->first();
        
        // Réservations du client
        $bookings = DB::connection('tenant')->table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->where('bookings.customer_id', $customer->id)
            ->select('bookings.*', 'rooms.room_number', 'rooms.type')
            ->orderBy('bookings.check_in_date', 'desc')
            ->get();
        
        // Réservation active (si le client est actuellement dans l'hôtel)
        $activeBooking = DB::connection('tenant')->table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->where('bookings.customer_id', $customer->id)
            ->where('bookings.status', 'confirmed')
            ->where('bookings.check_in_date', '<=', now())
            ->where('bookings.check_out_date', '>=', now())
            ->select('bookings.*', 'rooms.room_number', 'rooms.type')
            ->first();
        
        // Services disponibles dans la filiale
        $services = DB::connection('tenant')->table('services')
            ->where('branch_id', $customer->branch_id)
            ->get();
        
        return view('dashboards.customer', compact(
            'customer', 
            'branch', 
            'bookings', 
            'activeBooking', 
            'services'
        ));
    }
}
