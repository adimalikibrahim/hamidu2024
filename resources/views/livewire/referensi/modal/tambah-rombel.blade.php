<div>
    <div wire:ignore.self class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Data Kordinator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label for="guru" class="col-sm-3 col-form-label">Kompetensi Keahlian</label>
                        <div class="col-sm-9" wire:ignore>
                            <select id="tingkat" class="form-select" wire:model="guru" data-pharaonic="select2" data-component-id="{{ $this->id }}" data-parent="#tambahModal" data-placeholder="== Pilih Kompetensi Keahlian ==">
                                <option value="">== Pilih Kompetensi Keahlian ==</option>
                                @foreach ($pengajar as $p)
                                <option value="{{$p->guru_id}}">{{$p->nama}}</option>
                                @endforeach
                                {{-- @dd($pengajar) --}}
                                {{-- @foreach ($pengajar as $jurusan)
                                <option value="{{$jurusan->jurusan_id}}">{{$jurusan->nama_jurusan_sp}}</option>
                                @endforeach --}}
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label for="kurikulum_id" class="col-sm-3 col-form-label">Kurikulum</label>
                        <div class="col-sm-9" wire:ignore>
                            <select id="kurikulum_id" class="form-select" wire:model="kurikulum_id" data-pharaonic="select2" data-component-id="{{ $this->id }}" data-parent="#tambahModal" data-placeholder="== Pilih Kurikulum ==">
                                <option value="">== Pilih Kurikulum ==</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$emit('postAdded')" class="btn btn-danger mr-auto">Tambah Form</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" wire:click.prevent="store()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
