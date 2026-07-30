@extends('layouts.admin')

@section('title', 'Editar plantilla')
@section('heading', 'Editar plantilla')
@section('subheading', $template->brand.' '.$template->model.' — '.$template->title)

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('model-templates.update', $template) }}">
            @csrf @method('PUT')
            @include('admin.model-templates._form', ['template' => $template])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('model-templates.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
