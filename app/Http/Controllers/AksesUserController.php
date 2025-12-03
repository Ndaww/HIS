<?php

namespace App\Http\Controllers;

use App\Models\AksesUser;
use App\Http\Requests\StoreAksesUserRequest;
use App\Http\Requests\UpdateAksesUserRequest;
use App\Models\MasterMenu;
use App\Models\User;

class AksesUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('pages.master.akses.index', compact('users'));
    }

    public function getMenu($userId)
    {
        $menus = MasterMenu::all();
        $userMenus = AksesUser::where('user_id', $userId)->pluck('menu_id')->toArray();

        return response()->json([
            'menus' => $menus,
            'userMenus' => $userMenus
        ]);
    }

    public function getMenuDt($userId)
    {
        $menus = MasterMenu::select('id', 'name');
        $userMenus = AksesUser::where('user_id', $userId)->pluck('menu_id')->toArray();

        return datatables()->of($menus)
            ->addIndexColumn()
            ->addColumn('is_checked', function($row) use ($userMenus) {
                return in_array($row->id, $userMenus);
            })
            ->make(true);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAksesUserRequest $request,$userId)
    {
    // hapus akses lama
    AksesUser::where('user_id', $userId)->delete();

    // simpan akses baru
    if ($request->menus) {
        foreach ($request->menus as $menuId) {
            AksesUser::create([
                'user_id' => $userId,
                'menu_id' => $menuId
            ]);
        }
    }

    return response()->json(['status' => 'success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(AksesUser $aksesUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AksesUser $aksesUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAksesUserRequest $request, AksesUser $aksesUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AksesUser $aksesUser)
    {
        //
    }
}
