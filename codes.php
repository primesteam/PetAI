<?php include_once "ajax/connect.php" ?>
<style>
    #feeders {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    #feeders td, #feeders th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    #feeders tr:nth-child(even){background-color: #f2f2f2;}

    #feeders tr:hover {background-color: #ddd;}

    #feeders th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
    }
</style>
<table id="feeders">
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Location</th>
        <th>Capacity (L)</th>
        <th>Capacity Time</th>
        <th>Temperature</th>
        <th>Voltage</th>
        <th>Last Update</th>
        <th>JSON</th>
    </tr>
    <?php
    $stmt = $db->prepare("SELECT * FROM petai.feeder");
    $stmt->execute();
    $feeders = $stmt->fetchAll();

    foreach ($feeders as $feeder) {
        $stmt = $db->prepare("SELECT * FROM petai.logs WHERE feeder=:id ORDER BY created DESC");
        $stmt->bindParam(':id', $feeder['id']);
        $stmt->execute();
        $logs = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM petai.feeder_data WHERE feeder=:id");
        $stmt->bindParam(':id', $feeder['id']);
        $stmt->execute();
        $data = $stmt->fetchAll(); ?>
        <td><?php echo $feeder['id'] ?></td>
        <td><?php echo $feeder['code'] ?></td>
        <td><?php echo $feeder['location'] ?></td>
        <td><?php echo $data[0]['capacity'] ?></td>
        <td><?php echo $data[0]['created'] ?></td>
        <td><?php echo $logs[0]['temperature'] ?></td>
        <td><?php echo $logs[0]['voltage'] ?></td>
        <td><?php echo $logs[0]['created'] ?></td>
        <td><a href="https://petai.labaki.gr/ajax/getfeeder.php?code=<?php echo $feeder['code'] ?>" target="_blank">New Windows</a></td>
    <?php } ?>
</table>