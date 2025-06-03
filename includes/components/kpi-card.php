<?php
// includes/components/kpi-card.php
// Expects:
//   $title (string), e.g. 'Avg Score'
//   $iconClass (string), e.g. 'fas fa-chart-pie text-blue-500'
//   $value (int or float), e.g. 85
//   $chartId (string), e.g. 'chartAvgScore'
?>
<div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
  <div class="flex items-center justify-between w-full mb-2">
    <h2 class="text-lg font-semibold text-gray-700"><?= htmlspecialchars($title) ?></h2>
    <i class="<?= htmlspecialchars($iconClass) ?> text-xl"></i>
  </div>
  <canvas id="<?= htmlspecialchars($chartId) ?>" class="w-full h-40"></canvas>
  <p class="mt-2 text-center text-2xl font-semibold text-gray-800"><?= htmlspecialchars($value) ?>%</p>
</div>
