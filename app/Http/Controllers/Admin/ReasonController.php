<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\Lock\Reason;
use Illuminate\Http\Request;

class ReasonController extends Controller
{
    protected function show(Request $request) {
        $reasons = Reason::get()
            ->makeHidden(['created_at', 'updated_at']);

        if ($request->on_server) {
            return [
                'e' => 'success',
                'd' => $reasons->where('type', 'rule')->makeHidden(['comments', 'type'])
            ];
        }
        return $reasons;
    }
    protected function save(Request $request) {
        $this->validate($request, [
            'reasons.*.slug'        => 'required_if:type,rule',
            'reasons.*.description' => 'required',
        ]);

        Reason::truncate();

        foreach($request->reasons as $value) {
            if (is_null($value['slug'])) {
                $value['slug'] = '';
            }
            $reason = new Reason;
            $reason->slug = $value['slug'];
            $reason->type = $value['type'];
            $reason->description = $value['description'];
            $reason->penalties = $value['penalties'] ?? [];
            $reason->comments = $value['comments'] ?? [];
            $reason->save();
        }
    }
}
