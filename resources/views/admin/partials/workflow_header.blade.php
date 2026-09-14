@php
  $workflowTitle = $title ?? 'Tahap Workflow';
  $workflowSubtitle = $subtitle ?? '';
  $workflowBackUrl = $backUrl ?? '';
  $workflowBackLabel = $backLabel ?? 'Kembali';
@endphp

<div class="d-none"
     data-workflow-topbar
     data-title="{{ $workflowTitle }}"
     data-subtitle="{{ $workflowSubtitle }}"
     data-back-url="{{ $workflowBackUrl }}"
     data-back-label="{{ $workflowBackLabel }}"></div>
