 <!-- Content Right Sidebar -->
<div class="right-bar">
    <div id="sidebarRight">
        <div class="right-bar-inner">
            <div class="text-end position-relative">
                <a href="#" class="btn right-bar-btn waves-effect waves-circle btn btn-circle btn-danger btn-sm">
                    <i class="mdi mdi-close"></i>
                </a>
            </div>
            <div class="right-bar-content" id="AppAdmin">
                <form class="box no-shadow"  method="POST"  @submit.prevent="startDay" action="{{ route('day.start') }}">
                    <div class="box-header px-0" style="padding-top: 1.5rem; padding-bottom:1.5rem">
                        <h4 class="box-title fw-600">Commencez une journée !</h4>
                    </div>
                    <div class="box-body px-0">
                        <div class="form-group">
                            <label class="form-label">Taux du jour <sup class="text-danger">*</sup></label>
                            <input type="text" name="currencie_value" value="@lastRate" class="form-control" placeholder="Taux, ex:2850 en CDF" required>
                        </div>
                    </div>
                    <div class="box-footer px-0">
                        <button type="submit" class="btn btn-success btn-rounded" style="width:100%" data-bs-dismiss="modal"> 
                            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="mdi mdi-check me-2"></i> Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /.Content Right Sidebar -->
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/admin.js") }}"></script>
@endpush
