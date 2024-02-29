<div>
    @include('panels.breadcrumb')
    <div class="content-body">
        <div class="card">
            <div class="card-body">
                @include('components.navigasi-table')
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Nama</th>
                            <th class="text-center">NIK</th>
                            <th class="text-center">L/P</th>
                            <th class="text-center">Kordinator</th>
                            {{-- <th class="text-center">Email</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @if($collection->count())

                        @foreach($collection as $item)
                        <tr>
                            <td>{{$item->nama}}</td>
                            <td class="text-center">{{$item->nik}}</td>
                            <td class="text-center">{{$item->jenis_kelamin}}</td>
                            {{-- <td>{{($item->anggota_rombel) ? $item->anggota_rombel->rombongan_belajar->nama : '-'}}
                            </td> --}}
                            {{-- <td><input type="text" class="form-control" value="{{$item->guru->nama_lengkap}}"
                                    readonly></td> --}}
                            <td class="text-center">
                                <div class="input-group">
                                    {{-- <select class="form-select" data-pharaonic="select2" data-search-off="true" wire:model.defer="rom"> --}}
                                    <select class="form-select" wire:model.defer="rom" data-pharaonic="" data-component-id="{{ $this->id }}" style="width: 10px">
                                        <option selected>== Pilih Kordinator ==</option>
                                        @foreach ($kordinator as $k)
                                            <option value="{{ $k->rombongan_belajar_id }}">{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary" wire:click="kembali('{{ $item->peserta_didik_id }}')">Aktifkan</button>
                                </div>
                            </td>

                            {{-- <td>{{$item->email}}</td> --}}

                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td class="text-center" colspan="4">Tidak ada data untuk ditampilkan</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <div class="row justify-content-between mt-2">
                    <div class="col-6">
                        @if($collection->count())
                        <p>Menampilkan {{ $collection->firstItem() }} sampai {{ $collection->firstItem() +
                            $collection->count() - 1 }} dari {{ $collection->total() }} data</p>
                        @endif
                    </div>
                    <div class="col-6">
                        {{ $collection->onEachSide(1)->links('components.custom-pagination-links-view') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('components.loader')
</div>
<script>
        Livewire.on('disabled', event => {
        $('[data-pharaonic="select2"]').prop( "disabled", true ),
    })
</script>
