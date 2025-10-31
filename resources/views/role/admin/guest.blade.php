@extends('layouts.index')

@section('content')
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top-3">
        <h4 class="card-title mb-0 fw-semibold">👥 Daftar Guest</h4>
        <!-- Tombol Tambah Guest -->
        <button
            class="btn btn-light btn-sm fw-semibold shadow-sm px-3"
            x-data
            @click="$dispatch('open-modal', 'createGuestModal')">
            + Tambah Guest
        </button>
    </div>

    <!-- Modal Tambah Guest -->
    <x-modal name="createGuestModal" :show="false">
        <form action="{{ route('admin.guests.store') }}" method="POST" class="p-3">
            @csrf
            <h5 class="fw-semibold mb-3 text-primary">Tambah Guest Baru</h5>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama</label>
                <input type="text" name="name" class="form-control rounded-2" placeholder="Masukkan nama guest..." required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control rounded-2" placeholder="Masukkan nomor telepon..." required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control rounded-2" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary px-3" @click="$dispatch('close-modal', 'createGuestModal')">Batal</button>
                <button type="submit" class="btn btn-primary px-3">Simpan</button>
            </div>
        </form>
    </x-modal>

    <div class="card-body bg-light p-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white shadow-sm rounded-3">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
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
                            <td class="text-center fw-semibold">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $guest->name }}</td>
                            <td>{{ $guest->phone }}</td>
                            <td>{{ $guest->description ?? '-' }}</td>
                            <td>{{ $guest->creator?->name ?? '-' }}</td>
                            <td>{{ $guest->created_at->format('d-m-Y H:i') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Tombol Edit -->
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editGuestModal{{ $guest->id }}">
                                        <i class="ri-edit-2-line"></i> Edit
                                    </button>

                                    <!-- Tombol Pilih Produk -->
                                    <a href="{{ route('admin.produk.byGuest', $guest->id) }}"
                                       class="btn btn-sm btn-info px-3 fw-semibold text-white">
                                        Pilih Produk
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Guest -->
                        <div class="modal fade" id="editGuestModal{{ $guest->id }}" tabindex="-1" aria-labelledby="editGuestLabel{{ $guest->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow-lg rounded-3">

                            <!-- Header -->
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-semibold" id="editGuestLabel{{ $guest->id }}">
                                ✏️ Edit Data Guest
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <!-- Body -->
                            <form action="{{ route('admin.guests.update', $guest->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body bg-light">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama</label>
                                    <input type="text" name="name" class="form-control form-control-lg rounded-2" value="{{ $guest->name }}" required>
                                    </div>

                                    <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control form-control-lg rounded-2" value="{{ $guest->phone }}" required>
                                    </div>

                                    <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea name="description" class="form-control rounded-2" rows="3" placeholder="Keterangan tambahan...">{{ $guest->description }}</textarea>
                                    </div>
                                </div>

                                </div>

                                <!-- Footer -->
                                <div class="modal-footer bg-white mt-5">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    💾 Simpan Perubahan
                                </button>
                                </div>
                            </form>

                            </div>
                        </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data guest</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $guests->links() }}
        </div>
    </div>
</div>
@endsection
