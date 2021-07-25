<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Server\AdminController as ServerAdminController;
use App\Http\Controllers\User\TrackController as UserTrackController;
use App\Http\Controllers\Controller;
use App\Models\Core\Lock;
use App\Models\Core\Lock\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Models\User;

class LockController extends Controller
{
    protected function show(Request $request) {
        $type = $request->get('type', 'ban');
        // return Lock::whereIsFirst(true)->paginate(15)->toArray();

        return Lock::with([
            'formatted_reason',
            'user',
            'executor',
            'history'
        ])
            ->whereType($type)
            ->withCount('history')
            ->orderBy('id','desc')
            ->paginate(15);
    }
    protected function create(Request $request) {
        $type = $request->get('type', 'ban');
        $executor = $request->get('executor');

        // if ($executor) {
        //     $user = User::find($executor)->id;
        //     $executor_id = $user->id;
        // } else {
        $executor_id = $request->user()->id;
        // }
        if ($request->on_server) {
            $request->reason = json_decode($request->reason, true);
        }

        $data = new UserTrackController();
        $request->steamid = $request->steamid;
        // TODO: Maybe remove steam_check?
        // Chtobi mojno bilo banit vseh
        $user = $data->steam_check($request);
        // return $user;
        $request->executor_user_id = $executor_id;
        $request->user_id = $user['id'];
        $request->type = $type;
        $request->reason = $request->reason;
        $request->comment = $request->comment;

        $gate = '';
        $gate .= '!';
        $gate .= $type;
        $gate .= ' - ';
        $gate .= 'Подтверждение блокировки '
        . $user['username'] .
        ' на '
        . $request->length .
        ' минут по причине: '
        . implode(',', $request->reason);

        if (!empty($request->comment)) {
            $gate .= ' - ' . $request->comment;
        }
        
        abort_if(
            !$request->is_accepted,
            202,
            $gate
        );

        $request->length = $request->length * 60;
        // $user_id            = $request->input('user_id');
        // $type               = $request->input('type');
        // $reason             = $request->reason;
        // $comment            = $request->input('comment');
        // $executor_user_id   = $request->input('executor_user_id');
        // $length             = $request->input('length');

        $controller = new ServerAdminController();
        $result = $controller->lockCreate($request);

        Redis::publish('admin/refresh_locks', json_encode([
            'user_id' => $user['id'],
        ]));

        if ($request->on_server) {
            if ($result['status'] == 422) {
                return [
                    'message' => $result['d']
                ];
            }
            return [
                'e' => 'success',
                'd' => $result['d']
            ];
        }
        return response($result['d'], $result['status']);
    }

    protected function delete(Request $request) {
        // $data = new UserTrackController();
        // $request->steamid = $request->steamid;
        // TODO: Maybe remove steam_check?
        // Chtobi mojno bilo banit vseh
        // $user = $data->steam_check($request);
        // return $user;
        $request->executor_user_id = $request->user()->id;
        // $request->user_id = $user['id'];
        // $request->type = 'ban';
        $request->reason = $request->reason;
        $request->lock_id = $request->lock_id;

        $controller = new ServerAdminController();
        $result = $controller->lockDelete($request);

        return response($result['d'], $result['status']);
    }

    protected function history_show(Request $request, $lock_id) {
        // return Lock::whereIsFirst(true)->paginate(15)->toArray();

        return History::whereLockId($lock_id)
            ->with(['formatted_reason','user','executor'])
            ->orderBy('id','desc')
            ->get();
    }

    protected function get_proofs(Request $request, $lock_id) {
        return Lock::findOrFail($lock_id)
            ->proofs()
            ->with('user')
            ->get();
    }
    protected function upload_proof(Request $request, $lock_id) {
        $request->validate([
            'file'  => 'required|mimes:jpg,jpeg,png,gif,mp4,webm|max:'.env('MAX_KB_PROOF'),
        ]);

        if ($file = $request->file('file')) {
            $filePath = 'proofs/'.date('YmdHis') ."_". random_strings(6)."." . $file->getClientOriginalExtension();

            $lock = Lock::findOrFail($lock_id);

            $lock->proofs()->create([
                'name'      => $file->getClientOriginalName(),
                'user_id'   => $request->user()->id,
                'path'      => $filePath,
            ]);

            Storage::cloud()->put($filePath, file_get_contents($file));

            return response()->json(
                $this->get_proofs($request, $lock_id),
                200
            );
        }
        // dd($request->file);
    }
    protected function update_proof(Request $request, $lock_id, $proof_id) {
        dd($request->user());
    }
    protected function delete_proof(Request $request, $lock_id, $proof_id) {
		// Здесь общий middleware по проверке прав, так что нужно делать либо отдельный
		// либо в контроллере, типа удалить только свое или если главный админ то любой
        dd($request->user());
    }
}
