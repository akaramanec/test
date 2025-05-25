<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployerRequest;
use App\Http\Requests\OrderRequest;
use App\Jobs\OrderJob;
use App\Jobs\VisitorJob;
use App\Models\Bot\Employer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployerController extends Controller
{
    public function update(EmployerRequest $request)
    {
        /** @var Employer|null $employer */
        if ($employer = Employer::where('phone', $request->phone)->first()) {
            $employer->update($request->validated());
            return response()->json(['status' => 'ok'], Response::HTTP_OK);
        }
        return response()->json(['status' => 'error', 'message' => 'Employer not found'], \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(EmployerRequest $request)
    {
        /** @var Employer|null $employer */
        if ($employer = Employer::where('phone', $request->phone)->first()) {
            $employer->delete();
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['status' => 'error', 'message' => 'Employer not found'], \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
    }
}
