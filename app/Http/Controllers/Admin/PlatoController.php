<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plato;
use App\Models\Categoria;
use App\Models\Restaurante;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class PlatoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $platos = Plato::all();

        return view('admin.platos.index', compact('platos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Pluck : genera un array solamente con los valores (sin claves)
        $categorias = Categoria::pluck('nombre', 'id');

        $restaurantes = Restaurante::pluck('nombre', 'id');

        return view('admin.platos.create', compact('categorias', 'restaurantes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:platos',
            'descripcion' => 'required',
            'file' => 'image',
            'precio' => 'required',
            'categoria_id' => 'required',
            'restaurante_id' => 'required'
        ]);

        $plato = Plato::create($request->all());

        // mover la imágen de la carpeta temp a la carpeta public/storage/file
        if ($request->file('file')) {
            $url = Storage::put('platos', $request->file('file'));
            $plato->foto()->create([
                'url' => $url
            ]);
        }

        return redirect()->route('admin.platos.edit', $plato)->with('info', 'El plato se creó con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Plato  $plato
     * @return \Illuminate\Http\Response
     */
    public function show(Plato $plato)
    {
        return view('admin.platos.show', compact('plato'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Plato  $plato
     * @return \Illuminate\Http\Response
     */
    public function edit(Plato $plato)
    {
        $categorias = Categoria::pluck('nombre', 'id');

        $restaurantes = Restaurante::pluck('nombre', 'id');

        return view('admin.platos.edit', compact('plato', 'categorias', 'restaurantes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plato  $plato
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plato $plato)
    {
        //ignorar el plato actual
        $request->validate([
            'nombre' => "required|unique:platos,nombre,$plato->id",
            'descripcion' => 'required',
            'precio' => 'required',
            'categoria_id' => 'required',
            'restaurante_id' => 'required'
        ]);

        $plato->update($request->all());

        if ($request->file('file')) {
            $url = Storage::put('platos', $request->file('file'));

            if ($plato->foto) {
                Storage::delete($plato->foto->url);

                $plato->foto->update([
                    'url' => $url
                ]);
            } else {
                $plato->foto()->create([
                    'url' => $url
                ]);
            }
        }

        return redirect(route('admin.platos.edit', $plato))->with('info', 'El plato se actualizó con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Plato  $plato
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plato $plato)
    {
        // se puede borrar la foto de esta forma abajo o con el observer que esta en app/Observers/PlatoObserver
        //Storage::delete($plato->foto->url);
        $plato->delete();

        return redirect(route('admin.platos.index'))->with('info', 'El plato se eliminó con éxito');
    }
}