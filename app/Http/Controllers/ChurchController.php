<?php
// create controller for church
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Church;
use App\Models\ChurchResults;

class ChurchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // create store function
    public function store(Request $request)
    {
        $info = $request->all();
        // validate request
        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
        ]);
        //create slug
        $slug = $this->createSlug($info['name']);
        //if church exists, create result else create church
        $church = Church::where('slug', $slug)->first();
        if ($church) {
            // create result
            $result = ChurchResults::create([
                'church_id' => $church->id,
                'data' => json_encode($info['data']),
            ]);
            // return response
            return response()->json($result, 201);
        } else {
            // create church
            $church = Church::create([
                'name' => $info['name'],
                'slug' => $slug,
            ]);
            // create result
            $result = ChurchResults::create([
                'church_id' => $church->id,
                'data' => json_encode($info['data']),
            ]);
            // return response
            return response()->json($result, 201);
        }


        // return response
        return response()->json($church, 201);
    }

    // create allResults function, return the requested church and all results
    public function allResults($church)
    {
        $results = Church::where('slug', $church)->with('results')->first();
        $parsedResults = array_map(function($result) {
            return json_decode($result['data'], true);
        }, $results['results']->toArray());
        // return response where slug is equal to the church
        return response()->json($parsedResults, 200);       
    }

    private function createSlug($string) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
        return preg_replace('/-+/', '-', $slug);
    }
}
