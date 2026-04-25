<script>
function changeFontSize(multiplier) {
    // Targets the element with ID 'content-area'
    var content = document.getElementById("content-area");
    
    // If multiplier is 2, reset to default (1em). 
    // Otherwise, adjust by 0.2em increments.
    content.style.fontSize = multiplier === 2 ? "1em" : 
    (parseFloat(content.style.fontSize || 1) + (multiplier * 0.2)) + "em";
}
</script>

| Change Text Size |
<input type='button' value='Reset' onclick="changeFontSize(2)" />
<input type='button' value='&nbsp; + &nbsp;' onclick="changeFontSize(1)" />
<input type='button' value='&nbsp; - &nbsp;' onclick="changeFontSize(-1)" />

| <button onclick="window.print()">Print Page</button>