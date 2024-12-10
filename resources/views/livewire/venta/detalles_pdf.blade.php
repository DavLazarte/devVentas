<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de la Venta #{{ $venta->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        h1, h2 {
            text-align: center;
        }
        .venta-info, .cliente-info {
            margin-bottom: 20px;
        }
        .venta-info p, .cliente-info p {
            margin: 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .total p {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Detalle de la Venta</h1>
    <div class="venta-info">
        <p><strong>Venta ID:</strong> {{ $venta->id }}</p>
        <p><strong>Fecha:</strong> {{ $venta->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Tipo de Venta:</strong> {{ $venta->tipo_venta }}</p>
    </div>

    <div class="cliente-info">
        <h2>Información del Cliente</h2>
        <p><strong>Cliente:</strong> {{ $venta->persona->nombre }}</p>
        <p><strong>Contacto:</strong> {{ $venta->persona->telefono }}</p>
    </div>

    <h2>Detalles de los Productos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>{{ number_format($detalle->precio_venta, 2) }}</td>
                <td>{{ number_format($detalle->cantidad * $detalle->precio_venta, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p><strong>Total de la Venta:</strong> {{ number_format($venta->total_venta, 2) }}</p>
        <p><strong>Forma de Pago:</strong> {{ $venta->forma_de_pago }}</p>
        <p><strong>Pago:</strong> {{ number_format($venta->pago, 2) }}</p>
        <p><strong>Saldo:</strong> {{ number_format($venta->saldo, 2) }}</p>
    </div>
</body>
</html>