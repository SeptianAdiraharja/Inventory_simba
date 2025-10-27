    @extends('layouts.index')

    @section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Daftar Guest</h4>
            <!-- Tombol Tambah Guest -->
            <button
                class="btn btn-primary btn-sm"
                x-data
                @click="$dispatch('open-modal', 'createGuestModal')">
                + Tambah Guest
            </button>
        </div>

        <!-- Modal Tambah Guest -->
        <x-modal name="createGuestModal" :show="false">
            <form action="{{ route('admin.guests.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'createGuestModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </x-modal>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Phone</th>
                        <th>Description</th>
                        <th>Dibuat Oleh</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guests as $guest)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $guest->name }}</td>
                            <td>{{ $guest->phone }}</td>
                            <td>{{ $guest->description }}</td>
                            <td>{{ $guest->creator?->name ?? '-' }}</td>
                            <td>{{ $guest->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <!-- Tombol Edit -->
                                <button
                                    class="btn btn-sm btn-warning"
                                    x-data
                                    @click="$dispatch('open-modal', 'editGuestModal{{ $guest->id }}')">
                                    Edit
                                </button>

                                

                                <!-- Tombol Detail -->
                                <a href="{{ route('admin.produk.byGuest', $guest->id) }}"
                                class="btn btn-sm btn-info">
                                    Pilih Produk
                                </a>

                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <x-modal name="editGuestModal{{ $guest->id }}" :show="false">
                            <form action="{{ route('admin.guests.update', $guest->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label>Nama</label>
                                    <input type="text" name="name" class="form-control" value="{{ $guest->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $guest->phone }}" required>
                                </div>
                                <div class="mb-3">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control">{{ $guest->description }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'editGuestModal{{ $guest->id }}')">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </x-modal>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data guest</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $guests->links() }}
            </div>
        </div>
    </div>
    @endsection
