<div>
    <h3>Active Scooters</h3>
    <table border="1">
        <tr>
            <th>IP Address</th>
            <th>Connected At</th>
        </tr>
        @foreach ($scooters as $scooter)
            <tr>
                <td>{{ $scooter->scooter_ip }}</td>
                <td>{{ \Carbon\Carbon::parse($scooter->connected_at)->diffForHumans() }}</td>
            </tr>
        @endforeach
    </table>
</div>
