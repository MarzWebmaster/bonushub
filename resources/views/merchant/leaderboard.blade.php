@extends('layouts.app')
@section('title', 'Leaderboard - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Customer Leaderboard</h1>
            <p class="page-subtitle">See your top customers</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Customer</th>
                        <th>Points</th>
                        <th>Tier</th>
                    </tr>
                </thead>
                <tbody id="lb-table">
                    <tr><td colspan="4" class="text-center text-surface-400 py-8">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="lb-pagination" class="flex items-center justify-between px-4 py-3 border-t border-surface-200"></div>
    </div>
</div>

<script>
let lbPage = 1;

function loadLeaderboard(page) {
    lbPage = page;
    fetch("/merchant/api/leaderboard?per_page=10&page=" + page)
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const data = d.leaderboard.data;
            let h = "";
            const startNum = (d.leaderboard.current_page - 1) * d.leaderboard.per_page;
            data.forEach((e, i) => {
                let rank = startNum + i + 1;
                let medal = rank == 1 ? "\ud83e\udd47" : rank == 2 ? "\ud83e\udd48" : rank == 3 ? "\ud83e\udd49" : rank;
                let name = e.customer?.name || "Unknown";
                let tier = e.tier_per_merchant || "Basic";
                let cid = e.customer_id;
                h += "<tr data-cid=\"" + cid + "\" class=\"cursor-pointer hover:bg-bonus-50 transition-colors lb-row\">" +
                    "<td class=\"text-center text-lg\">" + medal + "</td>" +
                    "<td class=\"font-medium text-bonus-700\">" + name + "</td>" +
                    "<td class=\"font-bold text-bonus-600\">" + Number(e.points).toLocaleString() + "</td>" +
                    "<td><span class=\"badge-tier " + tier.toLowerCase() + "\">" + tier + "</span></td>" +
                    "</tr>";
            });
            document.getElementById("lb-table").innerHTML = h;

            let ph = "";
            let cp = d.leaderboard.current_page;
            let lp = d.leaderboard.last_page;
            if (lp > 1) {
                ph += "<div class=\"flex gap-1\">";
                ph += "<button onclick=\"loadLeaderboard(" + (cp - 1) + ")\" " + (cp <= 1 ? "disabled" : "") +
                    " class=\"px-3 py-1.5 text-sm rounded-lg border border-surface-200 " +
                    (cp <= 1 ? "text-surface-300 cursor-not-allowed" : "text-surface-700 hover:bg-bonus-50") + "\">Prev</button>";

                for (let p = 1; p <= lp; p++) {
                    if (p === cp) {
                        ph += "<span class=\"px-3 py-1.5 text-sm rounded-lg bg-bonus-500 text-white font-semibold\">" + p + "</span>";
                    } else {
                        ph += "<button onclick=\"loadLeaderboard(" + p + ")\" class=\"px-3 py-1.5 text-sm rounded-lg border border-surface-200 text-surface-700 hover:bg-bonus-50\">" + p + "</button>";
                    }
                }

                ph += "<button onclick=\"loadLeaderboard(" + (cp + 1) + ")\" " + (cp >= lp ? "disabled" : "") +
                    " class=\"px-3 py-1.5 text-sm rounded-lg border border-surface-200 " +
                    (cp >= lp ? "text-surface-300 cursor-not-allowed" : "text-surface-700 hover:bg-bonus-50") + "\">Next</button>";
                ph += "</div>";
                ph += "<span class=\"text-xs text-surface-500\">Showing " + ((cp - 1) * 10 + 1) + "-" + Math.min(cp * 10, d.leaderboard.total) + " of " + d.leaderboard.total + "</span>";
            }
            document.getElementById("lb-pagination").innerHTML = ph;
        });
}

loadLeaderboard(1);

document.getElementById("lb-table").addEventListener("click", function(e) {
    var row = e.target.closest(".lb-row");
    if (row && row.dataset.cid) {
        window.location = "/merchant/customers/" + row.dataset.cid;
    }
});
</script>
@endsection