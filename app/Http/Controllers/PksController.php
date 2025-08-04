<?php

namespace App\Http\Controllers;

use App\Models\Pks;
use App\Http\Requests\StorePksRequest;
use App\Http\Requests\UpdatePksRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    public function indexSubmitted(Request $request)
    {
        if ($request->ajax()) {
            $data = Pks::wherein('status', ['submitted','signed'])
            ->orWhere(function($query) {
                $query->where('status', 'rejected')
                    ->whereNotNull('draft_document');
            })
            ->latest();

            if ($request->start_date) {
                $data->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $data->whereDate('end_date', '<=', $request->end_date);
            }

            if ($request->status) {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('duration', fn($row) => $row->start_date . ' s/d ' . $row->end_date)
                ->editColumn('created_at', function($ticket){
                    return $ticket->created_at->format('d-m-Y H:i');
                })
                ->editColumn('status', function ($ticket) {
                    if($ticket->status === 'submitted') {
                        $badge = 'bg-info';
                    } else if ($ticket->status === 'verified'){
                        $badge = 'bg-primary';
                    } else if ($ticket->status === 'approved'){
                        $badge = 'bg-success';
                    } else if ($ticket->status === 'rejected'){
                        $badge = 'bg-danger';
                    } else if ($ticket->status === 'signed'){
                        $badge = 'bg-secondary';
                    } else {
                        $badge = '';
                    }
                    return '
                    <span class="badge '.$badge.'  text-capitalize">'.$ticket->status.'</span>
                    ';
                })
                ->addColumn('action', function ($row) {
                    if ($row->status === 'rejected' && $row->draft_document) {
                        return '
                            <button class="btn btn-warning btn-sm" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-reupload-draft">
                                <i class="ri ri-upload-2-line"></i> Upload Ulang Draft
                            </button>
                        ';
                    } else if ($row->status === 'signed'  && $row->final_document == null) {
                        return '<button class="btn btn-success btn-sm" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-upload-final">
                                    <i class="ri ri-upload-2-line"></i> Upload Final
                                </button>';
                    } else if ($row->status === 'signed' && $row->final_document != null) {
                        return '<button class="btn-sm btn-outline-success btn-sm disabled" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-upload-final">
                                    <i class="ri ri-check-double-line"></i> Selesai
                                </button>';
                     } else {
                        return '
                            <button class="btn btn-success btn-sm" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-verify"><i class="ri ri-check-line"></i></button>
                            <button class="btn btn-danger btn-sm" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-reject"><i class="ri ri-close-line"></i></button>
                        ';
                    }
                    
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }

        return view('pages.pks.index');
    }

    public function mypks(Request $request)
    {
        if ($request->ajax()) {
            $data = Pks::where('user_id', auth()->user()->id)->latest();

            if ($request->start_date) {
                $data->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $data->whereDate('end_date', '<=', $request->end_date);
            }

            if ($request->status) {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('duration', fn($row) => $row->start_date . ' s/d ' . $row->end_date)
                ->editColumn('created_at', function($ticket){
                    return $ticket->created_at->format('d-m-Y H:i');
                })
                ->editColumn('start_date', function($ticket){
                    return Carbon::parse($ticket->start_date)->format('d-m-Y');
                })
                ->editColumn('end_date', function($ticket){
                    return Carbon::parse($ticket->end_date)->format('d-m-Y');
                })
                ->editColumn('status', function ($ticket) {
                    if($ticket->status === 'submitted') {
                        $badge = 'bg-info';
                    } else if ($ticket->status === 'verified'){
                        $badge = 'bg-primary';
                    } else if ($ticket->status === 'approved'){
                        $badge = 'bg-success';
                    } else if ($ticket->status === 'rejected'){
                        $badge = 'bg-danger';
                    } else if ($ticket->status === 'signed'){
                        $badge = 'bg-secondary';
                    } else {
                        $badge = '';
                    }
                    return '
                    <span class="badge '.$badge.'  text-capitalize">'.$ticket->status.'</span>
                    ';
                })
                ->addColumn('action', function ($row) {
                    if ($row->status === 'rejected' && $row->draft_document == null ) {
                        return '
                            <button class="btn btn-warning btn-sm" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modal-resubmit">
                                <i class="ri ri-upload-2-line"></i> Upload Ulang
                            </button>
                        ';
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }

        return view('pages.pks.pks-saya');
    }

    public function approval(Request $request)
    {
        if ($request->ajax()) {
            $data = Pks::where('status', 'verified')->latest();

            if ($request->start_date) {
                $data->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $data->whereDate('end_date', '<=', $request->end_date);
            }

            if ($request->status) {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('start_date', fn($row) => Carbon::parse($row->start_date)->format('d-m-Y'))
                ->editColumn('end_date', fn($row) => Carbon::parse($row->end_date)->format('d-m-Y'))
                ->editColumn('created_at', fn($row) => $row->created_at->format('d-m-Y H:i'))
                ->addColumn('draft', fn($row) => '<a href="/storage/' . $row->draft_document . '" target="_blank">Lihat Draft</a>')
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-success btn-sm" data-id="'.$row->id.'" onclick="approve('.$row->id.')"><i class="ri ri-check-line"></i> </button>
                        <button class="btn btn-danger btn-sm" data-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#modal-reject-approval"><i class="ri ri-close-line"></i> </button>
                    ';
                })
                ->rawColumns(['draft', 'action'])
                ->make(true);
        }

        return view('pages.pks.pks-approval');
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view ('pages.pks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePksRequest $request)
    {
        $validated = $request->validate([
            'partner_name' => 'required|string|max:255',
            'cooperation_type' => 'required|string|max:255',
            'objective' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'initial_document' => 'required|file|mimes:pdf|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('initial_document')) {
            $filePath = $request->file('initial_document')->store('pks/initial', 'public');
        }

        $pks = Pks::create([
            'user_id' => auth()->user()->id,
            'partner_name' => $validated['partner_name'],
            'cooperation_type' => $validated['cooperation_type'],
            'objective' => $validated['objective'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'initial_document' => $filePath,
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'PKS berhasil disimpan.',
            'data' => $pks
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pks $pks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pks $pks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePksRequest $request, Pks $pks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pks $pks)
    {
        //
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'draft_document' => 'required|file|mimes:pdf|max:2048',
        ]);

        $pks = Pks::findOrFail($request->pks_id);

        // Upload draft
        $path = $request->file('draft_document')->store('pks/draft', 'public');

        // Update PKS
        $pks->update([
            'draft_document' => $path,
            'status' => 'verified',
        ]);

        return response()->json([
            'message' => 'PKS berhasil diverifikasi dan draft berhasil diunggah.'
        ]);
    }

    public function reject(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'note' => 'required|string|max:1000',
        ]);

        $pks = Pks::findOrFail($request->pks_id);

        $pks->update([
            'note' => $request->note,
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'PKS berhasil ditolak.',
        ]);
    }

    public function resubmit(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'initial_document' => 'required|file|mimes:pdf|max:2048',
        ]);

        $pks = Pks::where('id', $request->pks_id)
                ->where('user_id', auth()->user()->id)
                ->where('status', 'rejected')
                ->firstOrFail();

        $path = $request->file('initial_document')->store('pks/initial', 'public');

        $pks->update([
            'initial_document' => $path,
            'status' => 'submitted',
            'note' => null,
        ]);

        return response()->json(['message' => 'Dokumen berhasil diupload ulang, dan status dikembalikan ke submitted.']);
    }

    public function reuploadDraft(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'draft_document' => 'required|file|mimes:pdf|max:2048',
        ]);

        $pks = Pks::where('id', $request->pks_id)
                ->where('status', 'rejected')
                ->whereNotNull('draft_document')
                ->firstOrFail();

        $path = $request->file('draft_document')->store('pks/draft', 'public');

        $pks->update([
            'draft_document' => $path,
            'status' => 'verified',
            'note' => null, // reset alasan penolakan
        ]);

        return response()->json(['message' => 'Draft berhasil diupload ulang dan dikirim ulang ke Direksi.']);
    }


    public function approve(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
        ]);

        $pks = Pks::findOrFail($request->pks_id);

        if ($pks->status !== 'verified') {
            return response()->json(['message' => 'PKS ini tidak dalam status valid untuk disetujui.'], 400);
        }

        $pks->update(['status' => 'signed']);

        return response()->json(['message' => 'PKS berhasil disetujui.']);
    }

    public function rejectApproval(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'note' => 'required|string|max:1000',
        ]);

        $pks = Pks::findOrFail($request->pks_id);

        if ($pks->status !== 'verified') {
            return response()->json(['message' => 'PKS ini tidak bisa ditolak karena statusnya bukan "verified".'], 400);
        }

        $pks->update([
            'status' => 'rejected',
            'note' => $request->note,
        ]);

        return response()->json(['message' => 'PKS berhasil ditolak.']);
    }

    public function uploadFinal(Request $request)
    {
        $request->validate([
            'pks_id' => 'required|exists:pks,id',
            'final_document' => 'required|file|mimes:pdf|max:2048',
        ]);

        $pks = Pks::where('id', $request->pks_id)
                ->where('status', 'signed')
                ->firstOrFail();

        $path = $request->file('final_document')->store('pks/final', 'public');

        $pks->update([
            'final_document' => $path,
            'status' => 'signed',
        ]);

        return response()->json(['message' => 'Dokumen final berhasil diunggah dan PKS telah ditandatangani.']);
    }




}
